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
    const SERVICE_FREIGHT = TrackingServiceInterface::SERVICE_FREIGHT;
    const SERVICE_EXPRESS = TrackingServiceInterface::SERVICE_EXPRESS;
    const SERVICE_PARCEL_DE = TrackingServiceInterface::SERVICE_PARCEL_DE;
    const SERVICE_PARCEL_NL = TrackingServiceInterface::SERVICE_PARCEL_NL;
    const SERVICE_PARCEL_PL = TrackingServiceInterface::SERVICE_PARCEL_PL;
    const SERVICE_DSC = TrackingServiceInterface::SERVICE_DSC;
    const SERVICE_DGF = TrackingServiceInterface::SERVICE_DGF;
    const SERVICE_ECOMMERCE = TrackingServiceInterface::SERVICE_ECOMMERCE;

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
