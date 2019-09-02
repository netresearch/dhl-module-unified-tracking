<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model;

use Dhl\GroupTracking\Api\Data\TrackingStatusInterface;
use Dhl\GroupTracking\Api\Data\TrackingStatusInterfaceFactory;
use Dhl\GroupTracking\Api\TrackingInfoProviderInterface;
use Dhl\GroupTracking\Exception\TrackingException;
use Dhl\GroupTracking\Model\Config\ModuleConfig;
use Dhl\ShippingCore\Api\Pipeline\RequestTracksPipelineInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
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
     * @var TrackRequestBuilder
     */
    private $trackRequestBuilder;

    /**
     * @var RequestTracksPipelineInterface
     */
    private $trackingPipeline;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var TrackingStatusInterfaceFactory
     */
    private $trackingStatusFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TrackingInfoProvider constructor.
     *
     * @param TrackRequestBuilder $trackRequestBuilder
     * @param RequestTracksPipelineInterface $trackingPipeline
     * @param ModuleConfig $moduleConfig
     * @param ResolverInterface $resolver
     * @param TrackingStatusInterfaceFactory $trackingStatusFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        TrackRequestBuilder $trackRequestBuilder,
        RequestTracksPipelineInterface $trackingPipeline,
        ModuleConfig $moduleConfig,
        ResolverInterface $resolver,
        TrackingStatusInterfaceFactory $trackingStatusFactory,
        LoggerInterface $logger
    ) {
        $this->trackRequestBuilder = $trackRequestBuilder;
        $this->trackingPipeline = $trackingPipeline;
        $this->moduleConfig = $moduleConfig;
        $this->resolver = $resolver;
        $this->trackingStatusFactory = $trackingStatusFactory;
        $this->logger = $logger;
    }

    /**
     * Obtain carrier tracking details for given tracking number.
     *
     * @param string $trackingId
     * @param string $carrierCode
     * @param string $serviceName
     * @return TrackingStatusInterface
     * @throws TrackingException
     */
    public function getTrackingDetails(
        string $trackingId,
        string $carrierCode,
        string $serviceName
    ): TrackingStatusInterface {
        try {
            $this->trackRequestBuilder->setTrackingNumber($trackingId);
            $this->trackRequestBuilder->setCarrierCode($carrierCode);
            $trackRequest = $this->trackRequestBuilder->build();
        } catch (NoSuchEntityException $exception) {
            $this->logger->error($exception->getLogMessage());
            throw new TrackingException(__('Unable to load tracking details for tracking number %1.', $trackingId));
        }

        $artifactsContainer = $this->trackingPipeline->run($trackRequest->getStoreId(), [$trackRequest]);

        return $this->trackingStatusFactory->create(['trackingNumber' => $trackingId]);
    }
}
