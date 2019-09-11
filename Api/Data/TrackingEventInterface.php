<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Api\Data;

/**
 * Interface TrackingEventInterface
 *
 * Details for a tracking event.
 *
 * @api
 * @package Dhl\UnifiedTracking\Api
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
interface TrackingEventInterface
{
    /**
     * Obtain the location the event occurred at.
     *
     * @return string
     */
    public function getDeliveryLocation(): string;

    /**
     * Obtain the time the event occurred at.
     *
     * @return string
     */
    public function getDeliveryTime(): string;

    /**
     * Obtain the date the event occurred at.
     *
     * @return string
     */
    public function getDeliveryDate(): string;

    /**
     * Obtain event description.
     *
     * @return string
     */
    public function getActivity(): string;
}
