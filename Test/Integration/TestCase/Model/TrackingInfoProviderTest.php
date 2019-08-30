<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model;

use Dhl\ShippingCore\Test\Integration\Fixture\Data\AddressDe;
use Dhl\ShippingCore\Test\Integration\Fixture\Data\AddressInterface;
use Dhl\ShippingCore\Test\Integration\Fixture\Data\AddressUs;
use Dhl\ShippingCore\Test\Integration\Fixture\Data\SimpleProduct;
use Dhl\ShippingCore\Test\Integration\Fixture\Data\SimpleProduct2;
use Dhl\ShippingCore\Test\Integration\Fixture\ShipmentFixture;
use Exception;
use Magento\Sales\Model\Order\Shipment;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class TrackingInfoProviderTest
 *
 * @package Dhl\GroupTracking\Test\Integration
 * @author Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link   https://www.netresearch.de/
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class TrackingInfoProviderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TrackingInfoProvider
     */
    private $trackingInfoProvider;

    /**
     * Init object manager and test subject
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->trackingInfoProvider = $this->objectManager->create(TrackingInfoProvider::class);
    }

    /**
     * Make sure the method does not break when empty tracking properties are given.
     *
     * @test
     */
    public function checkReturnTypeWithEmptyArguments()
    {
        $trackingDetails = $this->trackingInfoProvider->getTrackingDetails('', '', '');
        self::assertSame([], $trackingDetails);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @magentoConfigFixture current_store shipping/origin/country_id DE
     *
     * @param Shipment $shipment
     * @param AddressInterface $address
     * @param $trackNumber
     */
    public function getTrackingDetails(Shipment $shipment, AddressInterface $address, $trackNumber)
    {
        $trackingDetails = $this->trackingInfoProvider->getTrackingDetails(
            $trackNumber,
            $shipment->getOrder()->getShippingMethod(),
            ''
        );

        self::assertInternalType('array', $trackingDetails);
        self::assertNotEmpty($trackingDetails);
        self::assertArrayHasKey('recipientPostalCode', $trackingDetails);
        self::assertArrayHasKey('shippingOriginCountry', $trackingDetails);
        self::assertArrayHasKey('languages', $trackingDetails);

        self::assertSame($address->getPostcode(), $trackingDetails['recipientPostalCode']);
        self::assertSame('DE', $trackingDetails['shippingOriginCountry']);
    }

    /**
     * @return Shipment[][]|AddressInterface[][]|string[][]
     * @throws Exception
     */
    public function dataProvider()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Shipment\TrackFactory $trackFactory */
        $trackFactory = $objectManager->create(Shipment\TrackFactory::class);

        // prepare a DE shipment data
        $addressDe = new AddressDe();
        $shipmentDe = ShipmentFixture::createShipment(
            $addressDe,
            [new SimpleProduct(), new SimpleProduct2()],
            'flatrate_flatrate'
        );
        $shipmentDe->save();

        $trackDe = $trackFactory->create();
        $trackDe->setCarrierCode('flatrate_flatrate')->setTrackNumber('123456')->setParentId($shipmentDe->getId());

        $shipmentDe->addTrack($trackDe);
        $shipmentDe->getTracksCollection()->save();

        // prepare a US shipment data
        $addressUs = new AddressUs();
        $shipmentUs = ShipmentFixture::createShipment(
            $addressUs,
            [new SimpleProduct(), new SimpleProduct2()],
            'flatrate_flatrate'
        );
        $shipmentUs->save();
        $trackUs = $trackFactory->create();
        $trackUs->setCarrierCode('flatrate_flatrate')->setTrackNumber('1234567')->setParentId($shipmentUs->getId());
        $shipmentUs->addTrack($trackUs);
        $shipmentUs->getTracksCollection()->save();

        return [
            'de_destination' => [
                'shipment' => $shipmentDe, 'address' => $addressDe, 'trackNumber' => $trackDe->getTrackNumber()
            ],
            'us_destination' => [
                'shipment' => $shipmentUs, 'address' => $addressUs, 'trackNumber' => $trackUs->getTrackNumber()
            ],
        ];
    }
}
