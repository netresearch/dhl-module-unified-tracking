<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Webservice\Pipeline\Stage;

use Dhl\Sdk\UnifiedTracking\Api\ServiceFactoryInterface;
use Dhl\Sdk\UnifiedTracking\Exception\DetailedServiceException;
use Dhl\Sdk\UnifiedTracking\Exception\ServiceException;
use Dhl\ShippingCore\Api\Data\Pipeline\ArtifactsContainerInterface;
use Dhl\ShippingCore\Api\Data\TrackRequest\TrackRequestInterface;
use Dhl\ShippingCore\Api\Pipeline\RequestTracksStageInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingConfigurationInterface;
use Dhl\UnifiedTracking\Model\Config\ModuleConfig;
use Dhl\UnifiedTracking\Webservice\Pipeline\ArtifactsContainer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order\Shipment;
use Psr\Log\LoggerInterface;

/**
 * Class SendRequestStage
 *
 * @package Dhl\UnifiedTracking\Webservice
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
     * @var TrackingConfigurationInterface[]
     */
    private $configurations;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * SendRequestStage constructor.
     *
     * @param ServiceFactoryInterface $serviceFactory
     * @param ModuleConfig $config
     * @param ResolverInterface $resolver
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     * @param TrackingConfigurationInterface[] $configurations
     */
    public function __construct(
        ServiceFactoryInterface $serviceFactory,
        ModuleConfig $config,
        ResolverInterface $resolver,
        LoggerInterface $logger,
        TimezoneInterface $timezone,
        $configurations = []
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->config = $config;
        $this->resolver = $resolver;
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->configurations = $configurations;
    }

    /**
     * Load the tracking configuration for the given carrier code.
     *
     * @param string $carrierCode The carrier code
     *
     * @return TrackingConfigurationInterface
     * @throws \InvalidArgumentException
     */
    private function getCarrierConfigurationByCode(string $carrierCode): TrackingConfigurationInterface
    {
        foreach ($this->configurations as $configuration) {
            if ($configuration->getCarrierCode() === $carrierCode) {
                return $configuration;
            }
        }

        throw new \InvalidArgumentException(
            "The tracking configuration for carrier $carrierCode is not available."
        );
    }

    /**
     * Perform action on given track requests.
     *
     * @param TrackRequestInterface[] $requests
     * @param ArtifactsContainerInterface|ArtifactsContainer $artifactsContainer
     *
     * @return TrackRequestInterface[]
     */
    public function execute(array $requests, ArtifactsContainerInterface $artifactsContainer): array
    {
        $sortedCarrierTrackingRequests = [];

        foreach ($requests as $request) {
            $salesTrack = $request->getSalesTrack();

            if ($salesTrack) {
                $sortedCarrierTrackingRequests[$salesTrack->getCarrierCode()][] = $request;
            }
        }

        foreach ($sortedCarrierTrackingRequests as $carrierCode => $carrierTrackingRequests) {
            /** @var TrackRequestInterface $trackingRequest */
            foreach ($carrierTrackingRequests as $trackingRequest) {
                /** @var Shipment $shipment */
                $shipment = $trackingRequest->getSalesShipment();

                try {
                    $carrierConfig = $this->getCarrierConfigurationByCode($carrierCode);
                    $logger = $carrierConfig->getLogger();
                    $serviceName = $carrierConfig->getServiceName();
                } catch (\InvalidArgumentException $e) {
                    $logger = $this->logger;
                    $serviceName = null;
                }

                try {
                    $trackingService = $this->serviceFactory->createTrackingService(
                        $this->config->getConsumerKey(),
                        $logger,
                        $this->timezone->scopeDate($artifactsContainer->getStoreId())->getTimezone()
                    );

                    $trackingInformation = $trackingService->retrieveTrackingInformation(
                        $trackingRequest->getTrackNumber(),
                        $serviceName,
                        $this->config->getShippingOriginCountry($artifactsContainer->getStoreId()),
                        $this->config->getShippingOriginCountry($artifactsContainer->getStoreId()),
                        $shipment->getShippingAddress()->getPostcode(),
                        substr($this->resolver->getLocale(), 0, 2)
                    );

                    foreach ($trackingInformation as $track) {
                        $artifactsContainer->addApiResponse(
                            $track->getTrackingId(),
                            $track->getSequenceNumber(),
                            $track
                        );
                    }
                } catch (DetailedServiceException $exception) {
                    $artifactsContainer->addError($trackingRequest->getTrackNumber(), $exception->getMessage());
                } catch (ServiceException $exception) {
                    $this->logger->error($exception->getMessage(), ['exception' => $exception]);
                    $artifactsContainer->addError($trackingRequest->getTrackNumber(), 'Web service request failed.');
                }
            }
        }

        return $requests;
    }
}
