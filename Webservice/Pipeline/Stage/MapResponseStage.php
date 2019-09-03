<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Webservice\Pipeline\Stage;

use Dhl\GroupTracking\Webservice\Pipeline\ArtifactsContainer;
use Dhl\GroupTracking\Webservice\Pipeline\ResponseDataMapper;
use Dhl\Sdk\GroupTracking\Api\Data\TrackResponseInterface;
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
     * @var ResponseDataMapper
     */
    private $responseDataMapper;

    /**
     * MapResponseStage constructor.
     *
     * @param ResponseDataMapper $responseDataMapper
     */
    public function __construct(
        ResponseDataMapper $responseDataMapper
    ) {
        $this->responseDataMapper = $responseDataMapper;
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
        $response = $artifactsContainer->getApiResponses();
        array_walk(
            $response,
            function (TrackResponseInterface $trackResponse) use ($artifactsContainer) {
                $trackingStatus = $this->responseDataMapper->createTrackResponse($trackResponse);
                $artifactsContainer->addTrackResponse($trackingStatus->getTrackingNumber(), $trackingStatus);
            }
        );

        return $requests;
    }
}
