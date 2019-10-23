<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Plugin\ParcelDe;

use Dhl\Sdk\UnifiedTracking\Api\Data\TrackResponseInterface;
use Dhl\Sdk\UnifiedTracking\Api\TrackingServiceInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingEventInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingStatusInterface;
use Dhl\UnifiedTracking\Model\Tracking\TrackingEvent;
use Dhl\UnifiedTracking\Model\Tracking\TrackingStatus;
use Dhl\UnifiedTracking\Webservice\Pipeline\ResponseDataMapper;

/**
 * Class GetTrackingDetails
 *
 * @package Dhl\UnifiedTracking\Plugin
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class AdjustTimezone
{
    /**
     * @param TrackingStatusInterface|TrackingStatus $trackingStatus
     * @param int $offset
     * @return TrackingStatusInterface
     */
    private function updateTrackingStatus(TrackingStatusInterface $trackingStatus, int $offset)
    {
        $dateString = sprintf('%sT%sZ', $trackingStatus->getDeliveryDate(), $trackingStatus->getDeliveryTime());

        try {
            $dateTime = new \DateTime($dateString);
            $timestamp = $dateTime->getTimestamp();
        } catch (\Exception $exception) {
            return $trackingStatus;
        }

        $dateTime->setTimestamp($timestamp - $offset);
        $trackingStatus->setData('deliverydate', $dateTime->format('Y-m-d'));
        $trackingStatus->setData('deliverytime', $dateTime->format('H:i:s'));

        return $trackingStatus;
    }

    /**
     * @param TrackingEventInterface|TrackingEvent $trackingEvent
     * @param int $offset
     * @return TrackingEventInterface
     */
    private function updateTrackingEvent(TrackingEventInterface $trackingEvent, int $offset)
    {
        $dateString = sprintf('%sT%sZ', $trackingEvent->getDeliveryDate(), $trackingEvent->getDeliveryTime());

        try {
            $dateTime = new \DateTime($dateString);
            $timestamp = $dateTime->getTimestamp();
        } catch (\Exception $exception) {
            return $trackingEvent;
        }

        $dateTime->setTimestamp($timestamp - $offset);
        $trackingEvent->setData('deliverydate', $dateTime->format('Y-m-d'));
        $trackingEvent->setData('deliverytime', $dateTime->format('H:i:s'));

        return $trackingEvent;
    }

    /**
     * At the latest API version 1.0.8, the DHL Paket times do not include time zone designators
     * and are thus treated as UTC. Apparently the times are actually CE(S)T so they need to be
     * transformed until this issue if fixed. DHL support response pending.
     *
     * @link https://bugs.nr/DHLGW-619
     *
     * @param ResponseDataMapper $responseDataMapper
     * @param TrackingStatusInterface $trackingStatus
     * @param TrackResponseInterface $trackingInformation
     * @return TrackingStatusInterface
     */
    public function afterCreateTrackResponse(
        ResponseDataMapper $responseDataMapper,
        TrackingStatusInterface $trackingStatus,
        TrackResponseInterface $trackingInformation
    ) {
        if ($trackingInformation->getService() !== TrackingServiceInterface::SERVICE_PARCEL_DE) {
            return $trackingStatus;
        }

        try {
            $tzBerlin = new \DateTimeZone('Europe/Berlin');
            $dateTime = new \DateTime();
            $offset = $tzBerlin->getOffset($dateTime);
        } catch (\Exception $exception) {
            return $trackingStatus;
        }

        $this->updateTrackingStatus($trackingStatus, $offset);
        foreach ($trackingStatus->getProgressDetail() as $trackingEvent) {
            $this->updateTrackingEvent($trackingEvent, $offset);
        }

        return $trackingStatus;
    }
}
