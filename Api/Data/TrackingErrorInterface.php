<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Api\Data;

use Magento\Framework\Phrase;

/**
 * Interface TrackingErrorInterface
 *
 * Error details for a tracking number.
 *
 * @package Dhl\UnifiedTracking\Api
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
interface TrackingErrorInterface
{
    /**
     * Obtain the tracking number.
     *
     * @return string
     */
    public function getTrackingNumber(): string;

    /**
     * Obtain tracking error message.
     *
     * @return Phrase|null
     */
    public function getErrorMessage();
}
