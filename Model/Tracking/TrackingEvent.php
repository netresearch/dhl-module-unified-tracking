<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Tracking;

use Dhl\UnifiedTracking\Api\Data\TrackingEventInterface;
use Magento\Framework\DataObject;

class TrackingEvent extends DataObject implements TrackingEventInterface
{
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
    private $deliveryLocation;

    /**
     * @var string
     */
    private $activity;

    /**
     * TrackingEvent constructor.
     * @param string $deliveryDate
     * @param string $deliveryTime
     * @param string $deliveryLocation
     * @param string $activity
     * @param mixed[] $data
     */
    public function __construct(
        string $deliveryDate,
        string $deliveryTime = '',
        string $deliveryLocation = '',
        string $activity = '',
        array $data = []
    ) {
        $this->deliveryDate = $deliveryDate;
        $this->deliveryTime = $deliveryTime;
        $this->deliveryLocation = $deliveryLocation;
        $this->activity = $activity;

        $data['deliverydate'] = $deliveryDate;
        $data['deliverytime'] = $deliveryTime;
        $data['deliverylocation'] = $deliveryLocation;
        $data['activity'] = $activity;

        parent::__construct($data);
    }

    /**
     * Obtain the date the event occurred at.
     *
     * @return string
     */
    #[\Override]
    public function getDeliveryDate(): string
    {
        return $this->deliveryDate;
    }

    /**
     * Obtain the time the event occurred at.
     *
     * @return string
     */
    #[\Override]
    public function getDeliveryTime(): string
    {
        return $this->deliveryTime;
    }

    /**
     * Obtain the location the event occurred at.
     *
     * @return string
     */
    #[\Override]
    public function getDeliveryLocation(): string
    {
        return $this->deliveryLocation;
    }

    /**
     * Obtain event description.
     *
     * @return string
     */
    #[\Override]
    public function getActivity(): string
    {
        return $this->activity;
    }
}
