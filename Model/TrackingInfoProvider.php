<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model;

use Dhl\ShippingCore\Api\Pipeline\RequestTracksPipelineInterface;
use Dhl\UnifiedTracking\Api\TrackingInfoProviderInterface;
use Dhl\UnifiedTracking\Exception\TrackingException;
use Dhl\UnifiedTracking\Webservice\Pipeline\ArtifactsContainer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Shipping\Model\Tracking\Result\AbstractResult;
use Psr\Log\LoggerInterface;

/**
 * Class TrackingInfoProvider
 *
 * @author Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link   https://www.netresearch.de/
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TrackingInfoProvider constructor.
     *
     * @param TrackRequestBuilder $trackRequestBuilder
     * @param RequestTracksPipelineInterface $trackingPipeline
     * @param LoggerInterface $logger
     */
    public function __construct(
        TrackRequestBuilder $trackRequestBuilder,
        RequestTracksPipelineInterface $trackingPipeline,
        LoggerInterface $logger
    ) {
        $this->trackRequestBuilder = $trackRequestBuilder;
        $this->trackingPipeline = $trackingPipeline;
        $this->logger = $logger;
    }

    public function getTrackingDetails(
        string $trackingId,
        string $carrierCode
    ): AbstractResult {
        try {
            $this->trackRequestBuilder->setTrackingNumber($trackingId);
            $this->trackRequestBuilder->setCarrierCode($carrierCode);
            $trackRequest = $this->trackRequestBuilder->build();
        } catch (NoSuchEntityException $exception) {
            $this->logger->error($exception->getLogMessage());
            throw new TrackingException(__('Unable to load tracking details for tracking number %1.', $trackingId));
        }

        /** @var ArtifactsContainer $artifactsContainer */
        $artifactsContainer = $this->trackingPipeline->run($trackRequest->getStoreId(), [$trackRequest]);
        $trackResponses = $artifactsContainer->getTrackResponses();
        $trackErrors = $artifactsContainer->getTrackErrors();

        return $trackErrors[$trackingId] ?? $trackResponses[$trackingId];
    }
}
