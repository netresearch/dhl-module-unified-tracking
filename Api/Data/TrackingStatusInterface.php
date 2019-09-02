<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Api\Data;

/**
 * Interface TrackingStatusInterface
 *
 * Details for a tracking number.
 *
 * @package Dhl\GroupTracking\Api
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
interface TrackingStatusInterface
{
    /**
     * Obtain the tracking number.
     *
     * @return string
     */
    public function getTrackingNumber(): string;

    /**
     * Obtain the carrier title.
     *
     * @return string
     */
    public function getCarrierTitle(): string;

    /**
     * Obtain the current delivery status.
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Obtain the delivery's dispatch date.
     *
     * @return string
     */
    public function getShippedDate(): string;

    /**
     * Obtain the shipment weight.
     *
     * @return float
     */
    public function getWeight(): float;

    /**
     * Obtain the delivery location.
     *
     * @return string
     */
    public function getDeliveryLocation(): string;

    /**
     * Obtain the receiver.
     *
     * @return string
     */
    public function getSignedBy(): string;

    /**
     * Obtain a list of tracking history events.
     *
     * @return TrackingEventInterface[]
     */
    public function getProgressDetail(): array;
}
