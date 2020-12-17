<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Pipeline\Stage;

use Dhl\Sdk\UnifiedTracking\Api\Data\TrackResponseInterface;
use Dhl\ShippingCore\Api\Data\Pipeline\ArtifactsContainerInterface;
use Dhl\ShippingCore\Api\Data\Pipeline\TrackRequest\TrackRequestInterface;
use Dhl\ShippingCore\Api\Pipeline\RequestTracksStageInterface;
use Dhl\UnifiedTracking\Model\Pipeline\ArtifactsContainer;
use Dhl\UnifiedTracking\Model\Pipeline\ResponseDataMapper;

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
            } elseif (array_key_exists($trackingNumber, $responses) && !empty($responses[$trackingNumber])) {
                // web service returned a match with the response
                /** @var TrackResponseInterface[] $tracks */
                $tracks = $responses[$trackingNumber];
                $track = array_shift($tracks);
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
