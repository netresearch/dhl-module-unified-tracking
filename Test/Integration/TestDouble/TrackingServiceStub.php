<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Test\Integration\TestDouble;

use Dhl\Sdk\GroupTracking\Api\Data\TrackResponseInterface;
use Dhl\Sdk\GroupTracking\Api\TrackingServiceInterface;
use Dhl\Sdk\GroupTracking\Exception\ServiceException;

/**
 * Class TrackingServiceStub
 *
 * @package Dhl\GroupTracking\Test\Integration\TestDouble
 * @author Christoph Aßmann <christoph.assmann@netresearch.de>
 * @link https://www.netresearch.de/
 */
class TrackingServiceStub implements TrackingServiceInterface
{
    /**
     * Regular API responses.
     *
     * @var TrackResponseInterface[]
     */
    public $trackResponses = [];

    /**
     * Service exception. Can be set to make the request fail.
     *
     * @var ServiceException
     */
    public $exception;

    /**
     * @param string $trackingNumber
     * @param string|null $service
     * @param string|null $requesterCountryCode
     * @param string|null $originCountryCode
     * @param string|null $recipientPostalCode
     * @param string $language
     * @return TrackResponseInterface[]
     * @throws ServiceException
     */
    public function retrieveTrackingInformation(
        string $trackingNumber,
        string $service = null,
        string $requesterCountryCode = null,
        string $originCountryCode = null,
        string $recipientPostalCode = null,
        string $language = 'en'
    ): array {
        if ($this->exception) {
            throw $this->exception;
        }

        return $this->trackResponses;
    }
}
