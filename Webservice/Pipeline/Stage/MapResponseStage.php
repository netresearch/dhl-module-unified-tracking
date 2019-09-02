<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Webservice\Pipeline\Stage;

use Dhl\GroupTracking\Api\Data\TrackingEventInterfaceFactory;
use Dhl\GroupTracking\Api\Data\TrackingStatusInterfaceFactory;
use Dhl\GroupTracking\Webservice\Pipeline\ArtifactsContainer;
use Dhl\Sdk\GroupTracking\Api\Data\PersonInterface;
use Dhl\ShippingCore\Api\Data\Pipeline\ArtifactsContainerInterface;
use Dhl\ShippingCore\Api\Data\TrackRequest\TrackRequestInterface;
use Dhl\ShippingCore\Api\Pipeline\RequestTracksStageInterface;

/**
 * Class MapResponseStage
 *
 * @package Dhl\GroupTracking\Webservice
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class MapResponseStage implements RequestTracksStageInterface
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
     * @param TrackingStatusInterfaceFactory $trackingStatusFactory
     * @param TrackingEventInterfaceFactory $trackingEventFactory
     */
    public function __construct(
        TrackingStatusInterfaceFactory $trackingStatusFactory,
        TrackingEventInterfaceFactory $trackingEventFactory
    ) {
        $this->trackingStatusFactory = $trackingStatusFactory;
        $this->trackingEventFactory = $trackingEventFactory;
    }

    /**
     * Perform action on given track requests.
     *
     * @param TrackRequestInterface[] $requests
     * @param ArtifactsContainerInterface|ArtifactsContainer $artifactsContainer
     * @return TrackRequestInterface[]
     */
    public function execute(array $requests, ArtifactsContainerInterface $artifactsContainer): array
    {
        $trackingResponses = $artifactsContainer->getApiResponses();

        foreach ($requests as $request) {
            $trackingInformation = $trackingResponses[$request->getTrackNumber()];

            $destinationAddress = [
                $trackingInformation->getDestinationAddress()->getAddressLocality(),
                $trackingInformation->getDestinationAddress()->getCountryCode(),
                $trackingInformation->getDestinationAddress()->getPostalCode(),
                $trackingInformation->getDestinationAddress()->getStreetAddress(),
            ];
            $destinationAddress = array_filter($destinationAddress);

            $signee = $trackingInformation->getProofOfDelivery()->getSignee();
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
                $deliveryLocation = [
                    $statusEvent->getLocation()->getAddressLocality(),
                    $statusEvent->getLocation()->getCountryCode(),
                    $statusEvent->getLocation()->getPostalCode(),
                    $statusEvent->getLocation()->getStreetAddress(),
                ];
                $deliveryLocation = array_filter($deliveryLocation);

                $trackingEvent = $this->trackingEventFactory->create([
                    'deliveryDate' => $statusEvent->getTimeStamp()->format('Y-m-d'),
                    'deliveryTime' => $statusEvent->getTimeStamp()->format('H:i:s'),
                    'deliveryLocation' => implode(' ', $deliveryLocation),
                    'activity' => $statusEvent->getDescription(),
                ]);
                $progressDetail[]= $trackingEvent;
            }

            $trackingStatus = $this->trackingStatusFactory->create([
                'trackingNumber' => $request->getTrackNumber(),
                'status' => $trackingInformation->getLatestStatus()->getDescription(),
                'weight' => $trackingInformation->getPhysicalAttributes()->getWeight(),
                'deliveryLocation' => implode(' ', $destinationAddress),
                'signedBy' => $signedBy,
                'progressDetail' => $progressDetail,
            ]);

            $artifactsContainer->addTrackResponse($request->getTrackNumber(), $trackingStatus);
        }

        return $requests;
    }
}
