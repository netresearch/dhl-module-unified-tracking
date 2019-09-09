<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Webservice\Pipeline\Stage;

use Dhl\UnifiedTracking\Webservice\Pipeline\ArtifactsContainer;
use Dhl\UnifiedTracking\Webservice\Pipeline\ResponseDataMapper;
use Dhl\ShippingCore\Api\Data\Pipeline\ArtifactsContainerInterface;
use Dhl\ShippingCore\Api\Data\TrackRequest\TrackRequestInterface;
use Dhl\ShippingCore\Api\Pipeline\RequestTracksStageInterface;

/**
 * Class MapResponseStage
 *
 * @package Dhl\UnifiedTracking\Webservice
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
        $errors = $artifactsContainer->getErrors();
        $responses = $artifactsContainer->getApiResponses();

        foreach ($requests as $request) {
            $trackingNumber = $request->getTrackNumber();
            if (array_key_exists($trackingNumber, $errors)) {
                // service exception
                $errorMessage = $errors[$trackingNumber];
                $error = __('An error occurred while retrieving tracking details for tracking number %1: %2', $trackingNumber, $errorMessage);
                $trackingStatus = $this->responseDataMapper->createErrorResponse($trackingNumber, $error);
                $artifactsContainer->addTrackError($trackingNumber, $trackingStatus);
            } elseif (array_key_exists($trackingNumber, $responses)) {
                // web service returned a match with the response
                $track = $responses[$trackingNumber];
                $trackingStatus = $this->responseDataMapper->createTrackResponse($track);
                $artifactsContainer->addTrackResponse($trackingNumber, $trackingStatus);
            } else {
                // web service returned no match with the response
                $error = __('No tracking details found for tracking number %1.', $trackingNumber);
                $trackingStatus = $this->responseDataMapper->createErrorResponse($trackingNumber, $error);
                $artifactsContainer->addTrackError($trackingNumber, $trackingStatus);
            }
        }

        return $requests;
    }
}
