<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Api;

use Dhl\GroupTracking\Api\Data\TrackingStatusInterface;

/**
 * Interface TrackingInfoProviderInterface
 *
 * Entry point for retrieving tracking data from the DHL web service.
 *
 * @package Dhl\GroupTracking\Api
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
     * @param string $serviceName
     * @return TrackingStatusInterface
     */
    public function getTrackingDetails(
        string $trackingId,
        string $carrierCode,
        string $serviceName
    ): TrackingStatusInterface;
}
