<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model;

use Dhl\ShippingCore\Api\Data\TrackRequest\TrackRequestInterface;
use Dhl\ShippingCore\Api\Data\TrackRequest\TrackRequestInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order\ShipmentRepository;

/**
 * Class TrackRequestBuilder
 *
 * @package Dhl\GroupTracking\Model
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class TrackRequestBuilder
{
    /**
     * @var string
     */
    private $trackingNumber;

    /**
     * @var string
     */
    private $carrierCode;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ShipmentTrackRepositoryInterface
     */
    private $trackRepository;

    /**
     * @var ShipmentRepositoryInterface|ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @var TrackRequestInterfaceFactory
     */
    private $trackRequestFactory;

    /**
     * TrackRequestBuilder constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ShipmentTrackRepositoryInterface $trackRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param TrackRequestInterfaceFactory $trackRequestFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ShipmentTrackRepositoryInterface $trackRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        TrackRequestInterfaceFactory $trackRequestFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->trackRepository = $trackRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->trackRequestFactory = $trackRequestFactory;
    }

    /**
     * Set tracking number to build the request for.
     *
     * @param string $trackingNumber
     */
    public function setTrackingNumber(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * Set the carrier code.
     *
     * @param string $carrierCode
     */
    public function setCarrierCode(string $carrierCode)
    {
        $this->carrierCode = $carrierCode;
    }

    /**
     * Create the track request.
     *
     * @return TrackRequestInterface
     * @throws NoSuchEntityException
     */
    public function build(): TrackRequestInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ShipmentTrackInterface::CARRIER_CODE, $this->carrierCode)
            ->addFilter(ShipmentTrackInterface::TRACK_NUMBER, $this->trackingNumber)
            ->create();
        $searchResult = $this->trackRepository->getList($searchCriteria);

        if ($searchResult->getTotalCount() === 0) {
            $message = __('No track found for carrier %1 with tracking number %2.', $this->carrierCode, $this->trackingNumber);
            throw new NoSuchEntityException($message);
        }

        $tracks = $searchResult->getItems();
        /** @var ShipmentTrackInterface $track */
        $track = array_shift($tracks);

        try {
            $shipment = $this->shipmentRepository->get($track->getParentId());
        } catch (InputException $exception) {
            throw new NoSuchEntityException(__('No shipment found with shipment ID %1', $track->getParentId()), $exception);
        }

        $trackRequest = $this->trackRequestFactory->create(
            [
                'storeId' => $shipment->getStoreId(),
                'trackNumber' => $track->getTrackNumber(),
                'salesShipment' => $shipment,
                'salesTrack' => $track
            ]
        );

        $this->trackingNumber = null;
        $this->carrierCode = null;

        return $trackRequest;
    }
}
