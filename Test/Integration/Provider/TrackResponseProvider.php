<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Test\Integration\Provider;

use Dhl\Sdk\UnifiedTracking\Api\Data\TrackResponseInterface;
use Dhl\Sdk\UnifiedTracking\Model\Tracking\Response\Address;
use Dhl\Sdk\UnifiedTracking\Model\Tracking\Response\EstimatedDelivery;
use Dhl\Sdk\UnifiedTracking\Model\Tracking\Response\Person;
use Dhl\Sdk\UnifiedTracking\Model\Tracking\Response\PhysicalAttributes;
use Dhl\Sdk\UnifiedTracking\Model\Tracking\Response\ShipmentEvent;
use Dhl\Sdk\UnifiedTracking\Model\Tracking\TrackResponse;

/**
 * Class TrackResponseProvider
 *
 * @package Dhl\UnifiedTracking\Test\Integration\Provider
 * @author Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link   https://www.netresearch.de/
 */
class TrackResponseProvider
{
    /**
     * @param string $trackingNumber
     * @return TrackResponseInterface
     * @throws \Exception
     */
    public static function createDeResponse(string $trackingNumber): TrackResponseInterface
    {
        $edd = new EstimatedDelivery(new \DateTime('2019-09-03 11:51:56.236396'));
        $originAddress = new Address('DE');
        $destinationAddress = new Address('DE');

        $latestStatus = new ShipmentEvent(
            new \DateTime('2019-08-30 08:59:00.000000'),
            'delivered',
            'Die Sendung wurde erfolgreich zugestellt.',
            'Die Sendung wurde erfolgreich zugestellt.',
            '',
            '',
            new Address('', '', 'Deutschland')
        );

        $statusEvents = [$latestStatus];
        foreach (['transit', 'pre-transit'] as $statusCode) {
            $statusEvents[]= new ShipmentEvent(
                new \DateTime('2019-08-30 07:42:00.000000'),
                $statusCode,
                'Die Sendung foo.',
                'Die Sendung foo.'
            );
        }

        $receiver = new Person('', '', '', 'Sharon Ship Shmoovie, Inc.');
        $physicalAttributes = new PhysicalAttributes(0.375, 'kg');

        $trackingInformation = new TrackResponse(
            $trackingNumber,
            0,
            'parcel-de',
            $latestStatus,
            1,
            $physicalAttributes,
            $destinationAddress,
            $originAddress,
            'DHL PAKET',
            $edd,
            null,
            $receiver,
            null,
            $statusEvents,
            ['123456789'],
            ['123456789']
        );

        return $trackingInformation;
    }
}
