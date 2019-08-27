<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Api;

/**
 * Interface TrackingInfoProviderInterface
 *
 * @package Dhl\GroupTracking\API
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
interface TrackingInfoProviderInterface
{
    /**
     * @param int $storeId
     * @return string
     */
    public function getTrackingDetails(string $trackingId, string $serviceName): string;
}
