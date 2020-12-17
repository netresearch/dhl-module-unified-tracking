<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Api;

use Dhl\Sdk\UnifiedTracking\Api\TrackingServiceInterface;
use Dhl\UnifiedTracking\Exception\TrackingException;
use Magento\Shipping\Model\Tracking\Result\AbstractResult;

/**
 * Interface TrackingInfoProviderInterface
 *
 * Entry point for retrieving tracking data from the DHL web service.
 *
 * @api
 */
interface TrackingInfoProviderInterface
{
    public const SERVICE_FREIGHT = TrackingServiceInterface::SERVICE_FREIGHT;
    public const SERVICE_EXPRESS = TrackingServiceInterface::SERVICE_EXPRESS;
    public const SERVICE_POST_DE = TrackingServiceInterface::SERVICE_POST_DE;
    public const SERVICE_PARCEL_DE = TrackingServiceInterface::SERVICE_PARCEL_DE;
    public const SERVICE_PARCEL_NL = TrackingServiceInterface::SERVICE_PARCEL_NL;
    public const SERVICE_PARCEL_PL = TrackingServiceInterface::SERVICE_PARCEL_PL;
    public const SERVICE_DSC = TrackingServiceInterface::SERVICE_DSC;
    public const SERVICE_DGF = TrackingServiceInterface::SERVICE_DGF;
    public const SERVICE_ECOMMERCE = TrackingServiceInterface::SERVICE_ECOMMERCE;

    /**
     * Obtain carrier tracking details for given tracking number.
     *
     * @param string $trackingId
     * @param string $carrierCode
     * @return AbstractResult
     * @throws TrackingException
     */
    public function getTrackingDetails(
        string $trackingId,
        string $carrierCode
    ): AbstractResult;
}
