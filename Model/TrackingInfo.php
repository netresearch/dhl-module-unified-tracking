<?php
/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\GroupTracking\Model;

use Dhl\GroupTracking\Api\TrackingInfoProviderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Dhl\GroupTracking\Model\Config\ModuleConfig;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;

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
     * @var ShipmentTrackRepositoryInterface
     */
    private $trackRepository;
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * TrackingInfo constructor.
     * @param ShipmentTrackRepositoryInterface $trackRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleConfig $moduleConfig
     * @param ResolverInterface $resolver
     */
    public function __construct(
        ShipmentTrackRepositoryInterface $trackRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleConfig $moduleConfig,
        ResolverInterface $resolver
    ) {
        $this->trackRepository = $trackRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleConfig = $moduleConfig;
        $this->resolver = $resolver;
    }
    /**
     * @param string $trackingId
     * @param string $carrierCode
     * @param string $serviceName
     * @return array
     */
    public function getTrackingDetails(string $trackingId, string $carrierCode, string $serviceName): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ShipmentTrackInterface::CARRIER_CODE, $carrierCode)
            ->addFilter(ShipmentTrackInterface::TRACK_NUMBER, $trackingId)
            ->create();
        $searchResult = $this->trackRepository->getList($searchCriteria);
        $tracks = $searchResult->getItems();
        if (!empty($tracks)) {
            /** @var ShipmentTrackInterface $track */
            $track = current($tracks);
            /** @var Shipment $shipment */
            $shipment = $this->shipmentRepository->get($track->getParentId());
        } else {
            return [];
        }
        $recipientPostalCode = $shipment->getShippingAddress()->getPostcode();
        $shippingOriginCountry = $this->moduleConfig->getShippingOriginCountry($shipment->getStoreId());
        $resolver = $this->resolver->getLocale();
        return [
            'recipientPostalCode' => $recipientPostalCode,
            'shippingOriginCountry' =>  $shippingOriginCountry,
            'languages' => $resolver
        ];
    }
}
