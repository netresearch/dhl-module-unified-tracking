<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model;

use Dhl\GroupTracking\Api\Data\TrackingEventInterface;
use Dhl\GroupTracking\Api\Data\TrackingStatusInterface;
use Magento\Shipping\Model\Tracking\Result\Status;

/**
 * Class TrackingStatus
 *
 * @package Dhl\GroupTracking\Model
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class TrackingStatus extends Status implements TrackingStatusInterface
{
    /**
     * @var string
     */
    private $trackingNumber;

    /**
     * @var string
     */
    private $carrierTitle;

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
    private $signedBy;

    /**
     * @var TrackingEventInterface[]
     */
    private $progressDetail;

    /**
     * TrackingStatus constructor.
     *
     * @param string $trackingNumber
     * @param string $carrierTitle
     * @param string $status
     * @param string $shippedDate
     * @param float|null $weight
     * @param string $deliveryLocation
     * @param string $signedBy
     * @param TrackingEventInterface[] $progressDetail
     * @param mixed[] $data
     */
    public function __construct(
        string $trackingNumber,
        string $carrierTitle = '',
        string $status = '',
        string $shippedDate = '',
        float $weight = null,
        string $deliveryLocation = '',
        string $signedBy = '',
        array $progressDetail = [],
        array $data = []
    ) {
        $this->trackingNumber = $trackingNumber;
        $this->carrierTitle = $carrierTitle;
        $this->status = $status;
        $this->shippedDate = $shippedDate;
        $this->weight = $weight;
        $this->deliveryLocation = $deliveryLocation;
        $this->signedBy = $signedBy;
        $this->progressDetail = $progressDetail;

        $data['tracking'] = $trackingNumber;
        $data['carrier_title'] = $carrierTitle;
        $data['status'] = $status;
        $data['shipped_date'] = $shippedDate;
        $data['weight'] = $weight;
        $data['delivery_location'] = $deliveryLocation;
        $data['signedby'] = $signedBy;
        $data['progressdetail'] = $progressDetail;

        parent::__construct($data);
    }

    /**
     * Obtain the tracking number.
     *
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * Obtain the carrier title.
     *
     * @return string
     */
    public function getCarrierTitle(): string
    {
        return $this->carrierTitle;
    }

    /**
     * Obtain the current delivery status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Obtain the delivery's dispatch date.
     *
     * @return string
     */
    public function getShippedDate(): string
    {
        return $this->shippedDate;
    }

    /**
     * Obtain the shipment weight.
     *
     * @return float|null
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * Obtain the delivery location.
     *
     * @return string
     */
    public function getDeliveryLocation(): string
    {
        return $this->deliveryLocation;
    }

    /**
     * Obtain the receiver.
     *
     * @return string
     */
    public function getSignedBy(): string
    {
        return $this->signedBy;
    }

    /**
     * Obtain a list of tracking history events.
     *
     * @return TrackingEventInterface[]
     */
    public function getProgressDetail(): array
    {
        return $this->progressDetail;
    }
}
