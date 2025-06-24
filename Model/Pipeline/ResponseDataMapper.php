<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Pipeline;

use Dhl\Sdk\UnifiedTracking\Api\Data\AddressInterface;
use Dhl\Sdk\UnifiedTracking\Api\Data\PersonInterface;
use Dhl\Sdk\UnifiedTracking\Api\Data\ShipmentEventInterface;
use Dhl\Sdk\UnifiedTracking\Api\Data\TrackResponseInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingErrorInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingErrorInterfaceFactory;
use Dhl\UnifiedTracking\Api\Data\TrackingEventInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingEventInterfaceFactory;
use Dhl\UnifiedTracking\Api\Data\TrackingStatusInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingStatusInterfaceFactory;
use Magento\Framework\Phrase;

class ResponseDataMapper
{
    /**
     * @var TrackingErrorInterfaceFactory
     */
    private $trackingErrorFactory;

    /**
     * @var TrackingEventInterfaceFactory
     */
    private $trackingEventFactory;

    /**
     * @var TrackingStatusInterfaceFactory
     */
    private $trackingStatusFactory;

    /**
     * MapResponseStage constructor.
     *
     * @param TrackingErrorInterfaceFactory $trackingErrorFactory
     * @param TrackingEventInterfaceFactory $trackingEventFactory
     * @param TrackingStatusInterfaceFactory $trackingStatusFactory
     */
    public function __construct(
        TrackingErrorInterfaceFactory $trackingErrorFactory,
        TrackingEventInterfaceFactory $trackingEventFactory,
        TrackingStatusInterfaceFactory $trackingStatusFactory
    ) {
        $this->trackingErrorFactory = $trackingErrorFactory;
        $this->trackingEventFactory = $trackingEventFactory;
        $this->trackingStatusFactory = $trackingStatusFactory;
    }

    /**
     * Extract date parts (date, time) from \DateTime object.
     *
     * It is important to change the time zone before extracting the date parts
     * because Magento will convert the date to the store's time zone for display
     * and expects the parts to be given in the default time zone (UTC).
     *
     * @param \DateTime $dateTime
     * @return string[]
     */
    private function getDateParts(\DateTime $dateTime): array
    {
        // do not modify the original \DateTime object
        $output = clone $dateTime;
        $output->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return [
            'date' => $output->format('Y-m-d'),
            'time' => $output->format('H:i:s'),
        ];
    }

    /**
     * Extract non-empty person properties.
     *
     * @param PersonInterface|null $person
     * @return string[]
     */
    private function mapPerson(?PersonInterface $person = null): array
    {
        if (!$person) {
            return [];
        }

        $data = [$person->getOrganization(), $person->getName(), $person->getGivenName(), $person->getFamilyName()];

        return array_filter($data);
    }

    /**
     * Extract non-empty address properties.
     *
     * @param AddressInterface|null $address
     * @return string[]
     */
    private function mapAddress(?AddressInterface $address = null): array
    {
        if (!$address) {
            return [];
        }

        $data = [
            $address->getAddressLocality(),
            $address->getCountryCode(),
            $address->getPostalCode(),
            $address->getStreetAddress(),
        ];

        return array_filter($data);
    }

    /**
     * Map a web service shipment event to an application tracking event.
     *
     * @param ShipmentEventInterface $shipmentEvent
     * @return TrackingEventInterface
     */
    private function mapStatusEvent(ShipmentEventInterface $shipmentEvent): TrackingEventInterface
    {
        $date = $this->getDateParts($shipmentEvent->getTimeStamp());
        $location = $this->mapAddress($shipmentEvent->getLocation());
        $trackingEvent = $this->trackingEventFactory->create(
            [
                'deliveryDate' => $date['date'],
                'deliveryTime' => $date['time'],
                'deliveryLocation' => implode(' ', $location),
                'activity' => $shipmentEvent->getDescription(),
            ]
        );

        return $trackingEvent;
    }

    /**
     * Create track response.
     *
     * @param TrackResponseInterface $trackingInformation
     * @return TrackingStatusInterface
     */
    public function createTrackResponse(TrackResponseInterface $trackingInformation): TrackingStatusInterface
    {
        $weight = $trackingInformation->getPhysicalAttributes()
            ? $trackingInformation->getPhysicalAttributes()->getWeight()
            : null;
        $progressDetail = array_map(
            function (ShipmentEventInterface $shipmentEvent) {
                return $this->mapStatusEvent($shipmentEvent);
            },
            $trackingInformation->getStatusEvents()
        );

        $latestStatus = $trackingInformation->getLatestStatus();
        $statusData = [
            'trackingNumber' => $trackingInformation->getTrackingId(),
            'trackSummary' => $latestStatus->getDescription(),
            'status' => $latestStatus->getStatusCode(),
            'weight' => $weight,
            'progressDetail' => $progressDetail,
        ];

        if ($latestStatus->getStatusCode() === ShipmentEventInterface::STATUS_CODE_DELIVERED) {
            $receiver = $this->mapPerson($trackingInformation->getReceiver());
            $date = $this->getDateParts($latestStatus->getTimeStamp());
            $signee = $trackingInformation->getProofOfDelivery()
                ? $trackingInformation->getProofOfDelivery()->getSignee()
                : null;
            $signedBy = $this->mapPerson($signee);

            $statusData['deliveryLocation'] = implode(' ', $receiver);
            $statusData['deliveryDate'] = $date['date'];
            $statusData['deliveryTime'] = $date['time'];
            $statusData['signedBy'] = implode(' ', $signedBy);
        }

        $trackingStatus = $this->trackingStatusFactory->create($statusData);

        return $trackingStatus;
    }

    /**
     * Create track response with error message.
     *
     * @param string $trackingNumber
     * @param Phrase $message
     * @return TrackingErrorInterface
     */
    public function createErrorResponse(string $trackingNumber, Phrase $message): TrackingErrorInterface
    {
        $statusData = [
            'trackingNumber' => $trackingNumber,
            'errorMessage' => $message,
        ];

        $trackingStatus = $this->trackingErrorFactory->create($statusData);

        return $trackingStatus;
    }
}
