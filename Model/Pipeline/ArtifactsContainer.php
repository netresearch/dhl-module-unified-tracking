<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Pipeline;

use Dhl\Sdk\UnifiedTracking\Api\Data\TrackResponseInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingErrorInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingStatusInterface;
use Netresearch\ShippingCore\Api\Data\Pipeline\ArtifactsContainerInterface;

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
     * Track errors suitable for processing by the core.
     *
     * @var TrackingErrorInterface[]
     */
    private $trackErrors = [];

    /**
     * Set store id for the pipeline.
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void
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
    public function addError(string $trackingNumber, string $errorMessage): void
    {
        $this->errors[$trackingNumber] = $errorMessage;
    }

    /**
     * Add a received response object.
     *
     * @param string $requestIndex
     * @param int $sequenceNumber
     * @param TrackResponseInterface $apiResponse
     * @return void
     */
    public function addApiResponse(string $requestIndex, int $sequenceNumber, TrackResponseInterface $apiResponse): void
    {
        if (!isset($this->apiResponses[$requestIndex])) {
            $this->apiResponses[$requestIndex] = [];
        }
        $this->apiResponses[$requestIndex][$sequenceNumber] = $apiResponse;
    }

    /**
     * Add a positive tracking status response.
     *
     * @param string $requestIndex
     * @param TrackingStatusInterface $trackResponse
     */
    public function addTrackResponse(string $requestIndex, TrackingStatusInterface $trackResponse): void
    {
        $this->trackResponses[$requestIndex] = $trackResponse;
    }

    /**
     * Add a tracking error response.
     *
     * @param string $requestIndex
     * @param TrackingErrorInterface $trackError
     */
    public function addTrackError(string $requestIndex, TrackingErrorInterface $trackError): void
    {
        $this->trackErrors[$requestIndex] = $trackError;
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
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtain the response objects as received from the web service.
     *
     * @return TrackResponseInterface[][]
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

    /**
     * Obtain the tracking errors.
     *
     * @return TrackingErrorInterface[]
     */
    public function getTrackErrors(): array
    {
        return $this->trackErrors;
    }
}
