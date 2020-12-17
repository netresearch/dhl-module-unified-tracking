<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Plugin\Carrier;

use Dhl\UnifiedTracking\Exception\TrackingException;
use Dhl\UnifiedTracking\Model\Tracking\TrackingStatus;
use Dhl\UnifiedTracking\Model\TrackingInfoProvider;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Tracking\Result\AbstractResult;
use Psr\Log\LoggerInterface;

class GetTrackingDetails
{
    /**
     * @var TrackingInfoProvider
     */
    private $trackingInfoProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetTrackingDetails constructor.
     *
     * @param TrackingInfoProvider $trackingInfoProvider
     * @param LoggerInterface $logger
     */
    public function __construct(TrackingInfoProvider $trackingInfoProvider, LoggerInterface $logger)
    {
        $this->trackingInfoProvider = $trackingInfoProvider;
        $this->logger = $logger;
    }

    /**
     * Replace the carrier's tracking result by tracking details from the web service.
     *
     * @param AbstractCarrierOnline $carrier
     * @param string|false|AbstractResult $result
     * @param string $trackingNumber
     * @return bool|AbstractResult
     */
    public function afterGetTrackingInfo(AbstractCarrierOnline $carrier, $result, string $trackingNumber)
    {
        if ($result !== false) {
            // return the carrier's tracking result if it provides one
            return $result;
        }

        try {
            /** @var TrackingStatus $details */
            return $this->trackingInfoProvider->getTrackingDetails($trackingNumber, $carrier->getCarrierCode());
        } catch (TrackingException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            return false;
        }
    }
}
