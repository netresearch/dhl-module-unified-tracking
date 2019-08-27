<?php
/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\GroupTracking\Model;

use Dhl\GroupTracking\Api\TrackingInfoProviderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

/**
 * Class TrackingInfo
 *
 * @package Dhl\GroupTracking\Model
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class TrackingInfo implements TrackingInfoProviderInterface
{
    /**
     * @var \Magento\Sales\Api\ShipmentTrackRepositoryInterface
     */
    private $trackRepository;
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * TrackingInfo constructor.
     * @param \Magento\Sales\Api\ShipmentTrackRepositoryInterface $trackRepository
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Sales\Api\ShipmentTrackRepositoryInterface $trackRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->trackRepository = $trackRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
    /**
     * @param string $trackingId
     * @param string $carrierCode
     * @param string $serviceName
     * @return string
     */
    public function getTrackingDetails(string $trackingId, string $carrierCode, string $serviceName): string
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(\Magento\Sales\Api\Data\ShipmentTrackInterface::CARRIER_CODE, $carrierCode)
            ->addFilter(\Magento\Sales\Api\Data\ShipmentTrackInterface::TRACK_NUMBER, $trackingId)
            ->create();
        $searchResult = $this->trackRepository->getList($searchCriteria);
        $tracks = $searchResult->getItems();
        if (!empty($tracks)) {
            /** @var ShipmentTrackInterface $track */
            $track = current($tracks);
            $shipment = $this->shipmentRepository->get($track->getParentId());
        } else {
            return "";
        }
    }
}
