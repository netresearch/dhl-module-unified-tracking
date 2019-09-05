<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Webservice\Pipeline;

use Dhl\GroupTracking\Api\Data\TrackingEventInterface;
use Dhl\GroupTracking\Api\Data\TrackingEventInterfaceFactory;
use Dhl\GroupTracking\Api\Data\TrackingErrorInterface;
use Dhl\GroupTracking\Api\Data\TrackingErrorInterfaceFactory;
use Dhl\GroupTracking\Api\Data\TrackingStatusInterface;
use Dhl\GroupTracking\Api\Data\TrackingStatusInterfaceFactory;
use Dhl\Sdk\GroupTracking\Api\Data\AddressInterface;
use Dhl\Sdk\GroupTracking\Api\Data\PersonInterface;
use Dhl\Sdk\GroupTracking\Api\Data\ShipmentEventInterface;
use Dhl\Sdk\GroupTracking\Api\Data\TrackResponseInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class ResponseDataMapper
 *
 * @package Dhl\GroupTracking\Webservice
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class ResponseDataMapper
{
    /**
     * @var TimezoneInterface
     */
    private $date;

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
     * @param TimezoneInterface $date
     * @param TrackingErrorInterfaceFactory $trackingErrorFactory
     * @param TrackingEventInterfaceFactory $trackingEventFactory
     * @param TrackingStatusInterfaceFactory $trackingStatusFactory
     */
    public function __construct(
        TimezoneInterface $date,
        TrackingErrorInterfaceFactory $trackingErrorFactory,
        TrackingEventInterfaceFactory $trackingEventFactory,
        TrackingStatusInterfaceFactory $trackingStatusFactory
    ) {
        $this->date = $date;
        $this->trackingErrorFactory = $trackingErrorFactory;
        $this->trackingEventFactory = $trackingEventFactory;
        $this->trackingStatusFactory = $trackingStatusFactory;
    }

    /**
     * Extract localized date and time parts from \DateTime object.
     *
     * The date needs to be formatted according to the current scope (admin or store front). Note that `scopeDate`
     * does not accept a \DateTime object.
     *
     * @see \Magento\Framework\Stdlib\DateTime\TimezoneInterface::scopeDate
     * @link https://github.com/magento/magento2/issues/23359
     *
     * @param \DateTime $dateTime
     * @return string[]
     */
    private function getDateParts(\DateTime $dateTime)
    {
        $scopeDate = $this->date->scopeDate(null, $dateTime->getTimestamp());
        $date = $this->date->formatDate($scopeDate);
        $fullDate = $this->date->formatDate($scopeDate, \IntlDateFormatter::SHORT, true);
        $time = trim(str_replace($date, '', $fullDate), ' ,');

        return [
            'date' => $date,
            'time' => $time,
        ];
    }

    /**
     * Extract non-empty person properties.
     *
     * @param PersonInterface|null $person
     * @return string[]
     */
    private function mapPerson(PersonInterface $person = null): array
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
    private function mapAddress(AddressInterface $address = null): array
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

        $statusData = [
            'trackingNumber' => $trackingInformation->getId(),
            'trackSummary' => $trackingInformation->getLatestStatus()->getDescription(),
            'status' => $trackingInformation->getLatestStatus()->getStatusCode(),
            'weight' => $weight,
            'progressDetail' => $progressDetail,
        ];

        // todo(nr): replace by SDK constant
        if ($trackingInformation->getLatestStatus()->getStatusCode() === 'delivered') {
            $receiver = $this->mapPerson($trackingInformation->getReceiver());
            $date = $this->getDateParts($trackingInformation->getLatestStatus()->getTimeStamp());
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
