<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Tracking;

use Dhl\UnifiedTracking\Api\Data\TrackingErrorInterface;
use Magento\Framework\Phrase;
use Magento\Shipping\Model\Tracking\Result\Error;

/**
 * Class TrackingError
 *
 * @package Dhl\UnifiedTracking\Model
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class TrackingError extends Error implements TrackingErrorInterface
{
    /**
     * @var string
     */
    private $trackingNumber;

    /**
     * @var Phrase
     */
    private $errorMessage;

    /**
     * TrackingError constructor.
     *
     * @param string $trackingNumber
     * @param Phrase $errorMessage
     * @param mixed[] $data
     */
    public function __construct(
        string $trackingNumber,
        Phrase $errorMessage,
        array $data = []
    ) {
        $this->trackingNumber = $trackingNumber;
        $this->errorMessage = $errorMessage;

        $data['tracking'] = $trackingNumber;
        $data['error_message'] = $errorMessage;

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
     * Obtain tracking error message.
     *
     * @return Phrase
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
