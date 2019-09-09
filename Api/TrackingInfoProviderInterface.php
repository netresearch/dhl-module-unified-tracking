<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Api;

use Magento\Shipping\Model\Tracking\Result\AbstractResult;

/**
 * Interface TrackingInfoProviderInterface
 *
 * Entry point for retrieving tracking data from the DHL web service.
 *
 * @package Dhl\UnifiedTracking\Api
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
interface TrackingInfoProviderInterface
{
    /**
     * Obtain carrier tracking details for given tracking number.
     *
     * @param string $trackingId
     * @param string $carrierCode
     * @return AbstractResult
     */
    public function getTrackingDetails(
        string $trackingId,
        string $carrierCode
    ): AbstractResult;
}
