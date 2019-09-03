<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Webservice\Pipeline\Stage;

use Dhl\GroupTracking\Model\Config\ModuleConfig;
use Dhl\GroupTracking\Webservice\Pipeline\ArtifactsContainer;
use Dhl\Sdk\GroupTracking\Api\ServiceFactoryInterface;
use Dhl\ShippingCore\Api\Data\Pipeline\ArtifactsContainerInterface;
use Dhl\ShippingCore\Api\Data\TrackRequest\TrackRequestInterface;
use Dhl\ShippingCore\Api\Pipeline\RequestTracksStageInterface;
use Magento\Framework\Locale\ResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SendRequestStage
 *
 * @package Dhl\GroupTracking\Webservice
 * @author  Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link    https://www.netresearch.de/
 */
class SendRequestStage implements RequestTracksStageInterface
{
    /**
     * @var ServiceFactoryInterface
     */
    private $serviceFactory;

    /**
     * @var ModuleConfig
     */
    private $config;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $serviceNames;

    /**
     * SendRequestStage constructor.
     * @param ServiceFactoryInterface $serviceFactory
     * @param ModuleConfig $config
     * @param ResolverInterface $resolver
     * @param LoggerInterface $logger
     * @param string[] $serviceNames
     */
    public function __construct(
        ServiceFactoryInterface $serviceFactory,
        ModuleConfig $config,
        ResolverInterface $resolver,
        LoggerInterface $logger,
        array $serviceNames = []
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->config = $config;
        $this->resolver = $resolver;
        $this->logger = $logger;
        $this->serviceNames = $serviceNames;
    }

    /**
     * Perform action on given track requests.
     *
     * @param TrackRequestInterface[] $requests
     * @param ArtifactsContainerInterface|ArtifactsContainer $artifactsContainer
     * @return TrackRequestInterface[]
     */
    public function execute(array $requests, ArtifactsContainerInterface $artifactsContainer): array
    {
        foreach ($requests as $request) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $request->getSalesShipment();

            $trackingService = $this->serviceFactory->createTrackingService(
                $this->config->getConsumerKey(),
                $this->logger
            );

            $trackingInformation = $trackingService->retrieveTrackingInformation(
                $request->getTrackNumber(),
                $this->serviceNames[$request->getSalesTrack()->getCarrierCode()] ?? null,
                $this->config->getShippingOriginCountry($artifactsContainer->getStoreId()),
                $this->config->getShippingOriginCountry($artifactsContainer->getStoreId()),
                $shipment->getShippingAddress()->getPostcode(),
                $this->resolver->getLocale()
            );

            $artifactsContainer->addApiResponse($request->getTrackNumber(), $trackingInformation);
        }

        return $requests;
    }
}
