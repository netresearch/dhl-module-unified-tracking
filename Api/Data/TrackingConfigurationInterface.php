<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Api\Data;

use Psr\Log\LoggerInterface;

/**
 * Interface TrackingConfigurationInterface
 *
 * @api
 * @package Dhl\UnifiedTracking\Api
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @link    https://www.netresearch.de/
 */
interface TrackingConfigurationInterface
{
    /**
     * Obtain the carrier code.
     *
     * @return string
     */
    public function getCarrierCode(): string;

    /**
     * Obtain the service name.
     *
     * @return string
     */
    public function getServiceName(): string;

    /**
     * Obtain the logger instance.
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface;
}
