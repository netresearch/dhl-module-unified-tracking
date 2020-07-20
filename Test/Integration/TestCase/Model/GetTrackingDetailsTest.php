<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model;

use Dhl\Sdk\UnifiedTracking\Api\Data\TrackResponseInterface;
use Dhl\Sdk\UnifiedTracking\Exception\DetailedServiceException;
use Dhl\Sdk\UnifiedTracking\Service\ServiceFactory;
use Dhl\UnifiedTracking\Api\Data\TrackingErrorInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingEventInterface;
use Dhl\UnifiedTracking\Api\Data\TrackingStatusInterface;
use Dhl\UnifiedTracking\Exception\TrackingException;
use Dhl\UnifiedTracking\Test\Integration\Provider\TrackResponseProvider;
use Dhl\UnifiedTracking\Test\Integration\TestDouble\TrackingServiceStub;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Shipping\Model\Tracking\Result\AbstractResult;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Sales\OrderBuilder;
use TddWizard\Fixtures\Sales\ShipmentBuilder;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class GetTrackingDetailsTest extends TestCase
{
    /**
     * @var ShipmentTrackInterface|Track
     */
    private static $track;

    /**
     * @var ObjectManagerInterface|\Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var TrackingServiceStub
     */
    private $trackingService;

    /**
     * Prepare object manager, set up web service stub.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();

        $this->trackingService = $this->objectManager->create(TrackingServiceStub::class);
        $serviceFactoryMock = $this->getMockBuilder(ServiceFactory::class)
                                   ->setMethods(['createTrackingService'])
                                   ->getMock();
        $serviceFactoryMock->method('createTrackingService')->willReturn($this->trackingService);
        $this->objectManager->addSharedInstance($serviceFactoryMock, ServiceFactory::class);
    }

    /**
     * Create order, shipment, track.
     *
     * @throws \Exception
     */
    public static function createTrackFixture()
    {
        $order = OrderBuilder::anOrder()
            ->withShippingMethod('flatrate_flatrate')
            ->withProducts(
                ProductBuilder::aSimpleProduct()->withSku('foo'),
                ProductBuilder::aSimpleProduct()->withSku('bar')
            )->build();
        $shipment = ShipmentBuilder::forOrder($order)->withTrackingNumbers('123456')->build();

        $tracks = $shipment->getTracks();
        self::$track = array_pop($tracks);
    }

    /**
     * Create API responses that match the requested tracking number.
     *
     * @return TrackResponseInterface[]
     * @throws \Exception
     */
    public function exactMatchDataProvider()
    {
        return [
            'api_returns_one_match' => [
                // delay access to track until the @magentoDataFixture ran through
                function () {
                    return self::$track;
                },
                function (string $requestedTrackingNumber) {
                    $trackResponses = [];
                    $responseId = $requestedTrackingNumber;

                    $trackResponses[$responseId] = TrackResponseProvider::createDeResponse($responseId);

                    return $trackResponses;
                },
            ],
        ];
    }

    /**
     * Create non-empty API responses that do not exactly match the requested tracking number (fuzzy search).
     *
     * @return TrackResponseInterface[]
     * @throws \Exception
     */
    public function noExactMatchDataProvider()
    {
        return [
            'api_returns_one_mismatch' => [
                // delay access to track until the @magentoDataFixture ran through
                function () {
                    return self::$track;
                },
                function (string $requestedTrackingNumber) {
                    $trackResponses = [];

                    foreach (['123'] as $suffix) {
                        $responseId = $suffix . $requestedTrackingNumber;
                        $trackResponses[$responseId] = TrackResponseProvider::createDeResponse($responseId);
                    }

                    return $trackResponses;
                },
            ],
        ];
    }

    /**
     * Scenario: The requested tracking number does not exist in the persistent storage.
     *
     * Assert that an exception is thrown.
     *
     * @test
     * @throws TrackingException
     */
    public function trackNotFound()
    {
        $trackingNumber = '123456';
        $carrierCode = 'foo';

        self::expectException(TrackingException::class);
        self::expectExceptionMessage("Unable to load tracking details for tracking number {$trackingNumber}");

        /** @var TrackingInfoProvider $trackingInfoProvider */
        $trackingInfoProvider = $this->objectManager->create(TrackingInfoProvider::class);
        $trackingInfoProvider->getTrackingDetails($trackingNumber, $carrierCode);
    }

    /**
     * Scenario: The web service returns one exact match for the requested tracking number.
     *
     * Assert that fields of the web service response are available in the tracking provider result.
     *
     * @test
     * @dataProvider exactMatchDataProvider
     * @magentoDataFixture createTrackFixture
     *
     * @param \Closure $getTrack Accessor to track creating in fixture.
     * @param \Closure $getTrackResponses Accessor to tracking responses built for the track fixture.
     * @throws \Exception
     */
    public function trackRequestSuccess(\Closure $getTrack, \Closure $getTrackResponses)
    {
        /** @var ShipmentTrackInterface|Track $track */
        $track = $getTrack();
        $carrierCode = strtok($track->getShipment()->getOrder()->getShippingMethod(), '_');

        /** @var TrackResponseInterface[] $trackResponses */
        $trackResponses = $getTrackResponses($track->getTrackNumber());
        $this->trackingService->trackResponses = $trackResponses;

        /** @var TrackingInfoProvider $trackingInfoProvider */
        $trackingInfoProvider = $this->objectManager->create(TrackingInfoProvider::class);
        $trackingDetails = $trackingInfoProvider->getTrackingDetails($track->getTrackNumber(), $carrierCode);

        self::assertInstanceOf(TrackingStatusInterface::class, $trackingDetails);
        self::assertInstanceOf(AbstractResult::class, $trackingDetails);

        $trackResponse = $trackResponses[$track->getTrackNumber()];
        self::assertSame($trackResponse->getTrackingId(), $trackingDetails->getTrackingNumber());
        self::assertSame($trackResponse->getLatestStatus()->getDescription(), $trackingDetails->getTrackSummary());
        self::assertSame($trackResponse->getLatestStatus()->getStatusCode(), $trackingDetails->getStatus());
        self::assertSame($trackResponse->getReceiver()->getName(), $trackingDetails->getDeliveryLocation());
        self::assertEmpty($trackingDetails->getErrorMessage());

        $progressDetail = $trackingDetails->getProgressDetail();
        self::assertInternalType('array', $progressDetail);
        self::assertContainsOnly(TrackingEventInterface::class, $progressDetail);
        self::assertCount(count($trackResponse->getStatusEvents()), $progressDetail);

        foreach ($progressDetail as $idx => $trackingEvent) {
            self::assertInternalType('string', $trackingEvent->getDeliveryDate());
            self::assertInternalType('string', $trackingEvent->getDeliveryTime());
            self::assertInternalType('string', $trackingEvent->getDeliveryLocation());
            self::assertInternalType('string', $trackingEvent->getActivity());
            self::assertSame($trackResponse->getStatusEvents()[$idx]->getDescription(), $trackingEvent->getActivity());
        }
    }

    /**
     * Scenario: The web service returns results but none match the requested tracking number.
     *
     * Assert that an error message is available in the tracking provider result.
     *
     * @test
     * @dataProvider noExactMatchDataProvider
     * @magentoDataFixture createTrackFixture
     *
     * @param \Closure $getTrack
     * @param \Closure $getTrackResponses
     * @throws \Exception
     */
    public function trackRequestNoMatch(\Closure $getTrack, \Closure $getTrackResponses)
    {
        /** @var ShipmentTrackInterface|Track $track */
        $track = $getTrack();
        $carrierCode = strtok($track->getShipment()->getOrder()->getShippingMethod(), '_');

        /** @var TrackResponseInterface[] $trackResponses */
        $trackResponses = $getTrackResponses($track->getTrackNumber());
        $this->trackingService->trackResponses = $trackResponses;

        /** @var TrackingInfoProvider $trackingInfoProvider */
        $trackingInfoProvider = $this->objectManager->create(TrackingInfoProvider::class);
        $trackingDetails = $trackingInfoProvider->getTrackingDetails($track->getTrackNumber(), $carrierCode);

        self::assertInstanceOf(TrackingErrorInterface::class, $trackingDetails);
        self::assertInstanceOf(AbstractResult::class, $trackingDetails);
        self::assertSame($track->getTrackNumber(), $trackingDetails->getTrackingNumber());
        self::assertInstanceOf(Phrase::class, $trackingDetails->getErrorMessage());
        self::assertSame(
            "No tracking details found for tracking number {$track->getTrackNumber()}.",
            $trackingDetails->getErrorMessage()->render()
        );
    }

    /**
     * Scenario: The web service returns a 404 status code, no matches for the requested tracking number.
     *
     * Assert that an error message is available in the tracking provider result.
     *
     * @test
     * @dataProvider exactMatchDataProvider
     * @magentoDataFixture createTrackFixture
     *
     * @param \Closure $getTrack
     * @throws \Exception
     */
    public function trackRequestNoResult(\Closure $getTrack)
    {
        $errorMessage = 'Web service request failed.';

        /** @var ShipmentTrackInterface|Track $track */
        $track = $getTrack();
        $carrierCode = strtok($track->getShipment()->getOrder()->getShippingMethod(), '_');

        $this->trackingService->exception = new DetailedServiceException($errorMessage, 404);

        /** @var TrackingInfoProvider $trackingInfoProvider */
        $trackingInfoProvider = $this->objectManager->create(TrackingInfoProvider::class);
        $trackingDetails = $trackingInfoProvider->getTrackingDetails($track->getTrackNumber(), $carrierCode);

        self::assertInstanceOf(TrackingErrorInterface::class, $trackingDetails);
        self::assertInstanceOf(AbstractResult::class, $trackingDetails);

        self::assertSame($track->getTrackNumber(), $trackingDetails->getTrackingNumber());
        self::assertInstanceOf(Phrase::class, $trackingDetails->getErrorMessage());
        self::assertStringEndsWith($errorMessage, $trackingDetails->getErrorMessage()->render());
    }
}
