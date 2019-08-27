<?php
/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\GroupTracking\Model;

use Dhl\GroupTracking\Api\TrackingInfoProviderInterface;

/**
 * Class TrackingInfo
 *
 * @package Dhl\GroupTracking\Model
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class TrackingInfo implements TrackingInfoProviderInterface
{

    /**
     * @param string $trackingId
     * @param string $serviceName
     * @return string
     */
    public function getTrackingDetails(string $trackingId, string $serviceName): string
    {

    }
}
