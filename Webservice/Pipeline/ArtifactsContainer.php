<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Webservice\Pipeline;

use Dhl\GroupTracking\Api\Data\TrackingStatusInterface;
use Dhl\Sdk\GroupTracking\Api\Data\TrackResponseInterface;
use Dhl\ShippingCore\Api\Data\Pipeline\ArtifactsContainerInterface;

/**
 * Class ArtifactsContainer
 *
 * @package Dhl\GroupTracking\Webservice
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class ArtifactsContainer implements ArtifactsContainerInterface
{
    /**
     * Store id the pipeline runs for.
     *
     * @var int|null
     */
    private $storeId;

    /**
     * Error messages occurred during pipeline execution.
     *
     * @var string[]
     */
    private $errors = [];

    /**
     * API (SDK) response objects.
     *
     * @var TrackResponseInterface[]
     */
    private $apiResponses = [];

    /**
     * Track response suitable for processing by the core.
     *
     * @var TrackingStatusInterface[]
     */
    private $trackResponses = [];

    /**
     * Set store id for the pipeline.
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Add error message for a tracking request.
     *
     * @param string $trackingNumber
     * @param string $errorMessage
     * @return void
     */
    public function addError(string $trackingNumber, string $errorMessage)
    {
        $this->errors[$trackingNumber] = $errorMessage;
    }

    /**
     * Add a received response object.
     *
     * @param string $requestIndex
     * @param TrackResponseInterface $apiResponse
     * @return void
     */
    public function addApiResponse(string $requestIndex, TrackResponseInterface $apiResponse)
    {
        $this->apiResponses[$requestIndex] = $apiResponse;
    }

    /**
     * Add a positive tracking status response.
     *
     * @param string $requestIndex
     * @param TrackingStatusInterface $trackResponse
     */
    public function addTrackResponse(string $requestIndex, TrackingStatusInterface $trackResponse)
    {
        $this->trackResponses[$requestIndex] = $trackResponse;
    }

    /**
     * Get store id for the pipeline.
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return (int) $this->storeId;
    }

    /**
     * Obtain error messages received from the web service.
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Obtain the response objects as received from the web service.
     *
     * @return TrackResponseInterface[]
     */
    public function getApiResponses(): array
    {
        return $this->apiResponses;
    }

    /**
     * Obtain the tracking status information.
     *
     * @return TrackingStatusInterface[]
     */
    public function getTrackResponses(): array
    {
        return $this->trackResponses;
    }
}
