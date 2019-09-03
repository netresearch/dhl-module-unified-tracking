<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Webservice\Pipeline;

use Dhl\GroupTracking\Api\Data\TrackingEventInterfaceFactory;
use Dhl\GroupTracking\Api\Data\TrackingStatusInterface;
use Dhl\GroupTracking\Api\Data\TrackingStatusInterfaceFactory;
use Dhl\Sdk\GroupTracking\Api\Data\PersonInterface;
use Dhl\Sdk\GroupTracking\Api\Data\TrackResponseInterface;

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
     * @var TrackingStatusInterfaceFactory
     */
    private $trackingStatusFactory;

    /**
     * @var TrackingEventInterfaceFactory
     */
    private $trackingEventFactory;

    /**
     * MapResponseStage constructor.
     *
     * @param TrackingStatusInterfaceFactory $trackingStatusFactory
     * @param TrackingEventInterfaceFactory $trackingEventFactory
     */
    public function __construct(
        TrackingStatusInterfaceFactory $trackingStatusFactory,
        TrackingEventInterfaceFactory $trackingEventFactory
    )
    {
        $this->trackingStatusFactory = $trackingStatusFactory;
        $this->trackingEventFactory = $trackingEventFactory;
    }

    /**
     * Create track response
     *
     * @param TrackResponseInterface $trackingInformation
     * @return TrackingStatusInterface
     */
    public function createTrackResponse(TrackResponseInterface $trackingInformation)
    {
        $destination = $trackingInformation->getDestinationAddress();
        if ($destination !== null) {
            $destinationAddress = [
                $trackingInformation->getDestinationAddress()->getAddressLocality(),
                $trackingInformation->getDestinationAddress()->getCountryCode(),
                $trackingInformation->getDestinationAddress()->getPostalCode(),
                $trackingInformation->getDestinationAddress()->getStreetAddress(),
            ];
        } else {
            $destinationAddress = [];
        }

        $destinationAddress = array_filter($destinationAddress);

        $proofOfDelivery = $trackingInformation->getProofOfDelivery();
        if ($proofOfDelivery !== null) {
            $signee = $trackingInformation->getProofOfDelivery()->getSignee();
        } else {
            $signee = null;
        }

        if (!$signee instanceof PersonInterface) {
            $signedBy = '';
        } else {
            $signee = [
                $signee->getOrganization(),
                $signee->getName(),
                $signee->getGivenName(),
                $signee->getFamilyName(),
            ];
            $signee = array_filter($signee);
            $signedBy = implode(' ', $signee);
        }

        $progressDetail = [];

        foreach ($trackingInformation->getStatusEvents() as $statusEvent) {
            $location = $statusEvent->getLocation();
            if ($location !== null) {
                $deliveryLocation = [
                    $statusEvent->getLocation()->getAddressLocality(),
                    $statusEvent->getLocation()->getCountryCode(),
                    $statusEvent->getLocation()->getPostalCode(),
                    $statusEvent->getLocation()->getStreetAddress(),
                ];
            } else {
                $deliveryLocation = [];
            }

            $deliveryLocation = array_filter($deliveryLocation);

            $trackingEvent = $this->trackingEventFactory->create([
                'deliveryDate' => $statusEvent->getTimeStamp()->format('Y-m-d'),
                'deliveryTime' => $statusEvent->getTimeStamp()->format('H:i:s'),
                'deliveryLocation' => implode(' ', $deliveryLocation),
                'activity' => $statusEvent->getDescription(),
            ]);
            $progressDetail[] = $trackingEvent;
        }

        $physicalAttributes = $trackingInformation->getPhysicalAttributes();
        if ($physicalAttributes !== null) {
            $weight = $trackingInformation->getPhysicalAttributes()->getWeight();
        } else {
            $weight = null;
        }

        $trackingStatus = $this->trackingStatusFactory->create([
            'trackingNumber' => $trackingInformation->getId(),
            'status' => $trackingInformation->getLatestStatus()->getDescription(),
            'weight' => $weight,
            'deliveryLocation' => implode(' ', $destinationAddress),
            'signedBy' => $signedBy,
            'progressDetail' => $progressDetail,
        ]);
        return $trackingStatus;
    }
}
