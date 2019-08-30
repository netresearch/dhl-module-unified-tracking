<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model;

use Dhl\GroupTracking\Api\TrackingInfoProviderInterface;
use Dhl\GroupTracking\Model\Config\ModuleConfig;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentRepository;
use Psr\Log\LoggerInterface;

/**
 * Class TrackingInfoProvider
 *
 * @package Dhl\GroupTracking\Model
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class TrackingInfoProvider implements TrackingInfoProviderInterface
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
     * @var ShipmentRepositoryInterface|ShipmentRepository
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TrackingInfoProvider constructor.
     *
     * @param ShipmentTrackRepositoryInterface $trackRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleConfig $moduleConfig
     * @param ResolverInterface $resolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShipmentTrackRepositoryInterface $trackRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleConfig $moduleConfig,
        ResolverInterface $resolver,
        LoggerInterface $logger
    ) {
        $this->trackRepository = $trackRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleConfig = $moduleConfig;
        $this->resolver = $resolver;
        $this->logger = $logger;
    }

    /**
     * Obtain carrier tracking details for given tracking number.
     *
     * @param string $trackingId
     * @param string $carrierCode
     * @param string $serviceName
     * @return string[]
     */
    public function getTrackingDetails(string $trackingId, string $carrierCode, string $serviceName): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ShipmentTrackInterface::CARRIER_CODE, $carrierCode)
            ->addFilter(ShipmentTrackInterface::TRACK_NUMBER, $trackingId)
            ->create();
        $searchResult = $this->trackRepository->getList($searchCriteria);

        if ($searchResult->getTotalCount() === 0) {
            return [];
        }

        $tracks = $searchResult->getItems();
        /** @var ShipmentTrackInterface $track */
        $track = array_shift($tracks);

        try {
            /** @var Shipment $shipment */
            $shipment = $this->shipmentRepository->get($track->getParentId());
        } catch (LocalizedException $exception) {
            $this->logger->error($exception->getLogMessage());
            return [];
        }

        $recipientPostalCode = $shipment->getShippingAddress()->getPostcode();
        $shippingOriginCountry = $this->moduleConfig->getShippingOriginCountry($shipment->getStoreId());
        $resolver = $this->resolver->getLocale();
        return [
            'recipientPostalCode' => $recipientPostalCode,
            'shippingOriginCountry' => $shippingOriginCountry,
            'languages' => $resolver
        ];
    }
}
