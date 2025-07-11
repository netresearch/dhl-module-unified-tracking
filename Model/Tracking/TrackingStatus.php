<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Tracking;

use Dhl\UnifiedTracking\Api\Data\TrackingEventInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingStatusInterface;
use Magento\Shipping\Model\Tracking\Result\Status;

class TrackingStatus extends Status implements TrackingStatusInterface
{
    /**
     * @var string
     */
    private $trackingNumber;

    /**
     * @var string
     */
    private $trackingUrl;

    /**
     * @var string
     */
    private $carrierTitle;

    /**
     * @var string
     */
    private $trackSummary;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $shippedDate;

    /**
     * @var float|null
     */
    private $weight;

    /**
     * @var string
     */
    private $deliveryLocation;

    /**
     * @var string
     */
    private $deliveryDate;

    /**
     * @var string
     */
    private $deliveryTime;

    /**
     * @var string
     */
    private $signedBy;

    /**
     * @var TrackingEventInterface[]
     */
    private $progressDetail;

    /**
     * TrackingStatus constructor.
     *
     * @param string $trackingNumber
     * @param string $trackingUrl
     * @param string $carrierTitle
     * @param string $trackSummary
     * @param string $status
     * @param string $shippedDate
     * @param float|null $weight
     * @param string $deliveryLocation
     * @param string $deliveryDate
     * @param string $deliveryTime
     * @param string $signedBy
     * @param TrackingEventInterface[] $progressDetail
     * @param mixed[] $data
     */
    public function __construct(
        string $trackingNumber,
        string $trackingUrl = '',
        string $carrierTitle = '',
        string $trackSummary = '',
        string $status = '',
        string $shippedDate = '',
        ?float $weight = null,
        string $deliveryLocation = '',
        string $deliveryDate = '',
        string $deliveryTime = '',
        string $signedBy = '',
        array $progressDetail = [],
        array $data = []
    ) {
        $this->trackingNumber = $trackingNumber;
        $this->trackingUrl = $trackingUrl;
        $this->carrierTitle = $carrierTitle;
        $this->trackSummary = $trackSummary;
        $this->status = $status;
        $this->shippedDate = $shippedDate;
        $this->weight = $weight;
        $this->deliveryLocation = $deliveryLocation;
        $this->deliveryDate = $deliveryDate;
        $this->deliveryTime = $deliveryTime;
        $this->signedBy = $signedBy;
        $this->progressDetail = $progressDetail;

        $data['tracking'] = $trackingNumber;
        $data['url'] = $trackingUrl;
        $data['carrier_title'] = $carrierTitle;
        $data['track_summary'] = $trackSummary;
        $data['status'] = $status;
        $data['shipped_date'] = $shippedDate;
        $data['weight'] = $weight;
        $data['delivery_location'] = $deliveryLocation;
        $data['deliverydate'] = $deliveryDate;
        $data['deliverytime'] = $deliveryTime;
        $data['signedby'] = $signedBy;
        $data['progressdetail'] = $progressDetail;

        parent::__construct($data);
    }

    /**
     * Obtain the tracking number.
     *
     * @return string
     */
    #[\Override]
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * Obtain the track&trace portal URL.
     *
     * @return string
     */
    #[\Override]
    public function getTrackingUrl(): string
    {
        return $this->trackingUrl;
    }

    /**
     * Obtain the carrier title.
     *
     * @return string
     */
    #[\Override]
    public function getCarrierTitle(): string
    {
        return $this->carrierTitle;
    }

    /**
     * Obtain the most recent tracking status description.
     *
     * @return string
     */
    #[\Override]
    public function getTrackSummary(): string
    {
        return $this->trackSummary;
    }

    /**
     * Obtain the current delivery status code.
     *
     * - pre-transit
     * - transit
     * - delivered
     * - failure
     * - unknown
     *
     * @return string
     */
    #[\Override]
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Obtain the delivery's dispatch date.
     *
     * @return string
     */
    #[\Override]
    public function getShippedDate(): string
    {
        return $this->shippedDate;
    }

    /**
     * Obtain the shipment weight.
     *
     * @return float|null
     */
    #[\Override]
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * Obtain the delivery location.
     *
     * @return string
     */
    #[\Override]
    public function getDeliveryLocation(): string
    {
        return $this->deliveryLocation;
    }

    /**
     * Obtain the
     * @return string
     */
    public function getDeliveryDate(): string
    {
        return $this->deliveryDate;
    }

    /**
     * @return string
     */
    public function getDeliveryTime(): string
    {
        return $this->deliveryTime;
    }

    /**
     * Obtain the receiver.
     *
     * @return string
     */
    #[\Override]
    public function getSignedBy(): string
    {
        return $this->signedBy;
    }

    /**
     * Obtain a list of tracking history events.
     *
     * @return TrackingEventInterface[]
     */
    #[\Override]
    public function getProgressDetail(): array
    {
        return $this->progressDetail;
    }
}
