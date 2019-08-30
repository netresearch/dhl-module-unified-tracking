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
 * Class TrackingInfoTest
 * @package Dhl\GroupTracking\Model
 * @author Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link   https://www.netresearch.de/
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class TrackingInfoTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var mixed
     */
    private $tracking;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
        /** @var TrackingInfo $trackingInfo */
        $this->tracking = $this->objectManager->create(TrackingInfo::class);
    }
    /**
     * @test
     */
    public function testGetTrackingDetailsWithoutEntities()
    {
        $trackingDetails = $this->tracking->getTrackingDetails('', '', '');
        self::assertSame([], $trackingDetails);
    }

    /**
     * @test
     * @dataProvider shipmentProvider
     * @magentoConfigFixture current_store shipping/origin/country_id DE
     * @param Shipment $shipment
     * @param AddressInterface $address
     * @param $trackNumber
     */
    public function testGetTrackingDetailsWithEntities(Shipment $shipment, AddressInterface $address, $trackNumber)
    {
        $trackingDetails = $this->tracking->getTrackingDetails($trackNumber, $shipment->getOrder()->getShippingMethod(), '');
        self::assertSame([
            'recipientPostalCode' => $address->getPostcode(),
            'shippingOriginCountry' =>'DE',
            'languages' => 'en_US'
        ], $trackingDetails);
    }
    /**
     * @return array
     * @throws Exception
     */
    public function shipmentProvider()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Shipment\TrackFactory $trackFactory */
        $trackFactory = $objectManager->create(Shipment\TrackFactory::class);
        $track = $trackFactory->create();

        $shipments = [];

        $recipientAddress = new AddressDe();
        $shipment = ShipmentFixture::createShipment(
            $recipientAddress,
            [new SimpleProduct(), new SimpleProduct2()],
            'flatrate_flatrate'
        );
        $shipment->save();
        $track->setCarrierCode('flatrate_flatrate')->setTrackNumber('123456')->setParentId($shipment->getId());
        $shipment->addTrack($track);
        $shipment->getTracksCollection()->save();

        $shipments['germanAddress'] = ['shipment' => $shipment, 'address' => $recipientAddress, 'trackNumber' => $track->getTrackNumber()];

        $recipientAddress = new AddressUs();
        $shipment = ShipmentFixture::createShipment(
            $recipientAddress,
            [new SimpleProduct(), new SimpleProduct2()],
            'flatrate_flatrate'
        );
        $shipment->save();
        $track = $trackFactory->create();
        $track->setCarrierCode('flatrate_flatrate')->setTrackNumber('1234567')->setParentId($shipment->getId());
        $shipment->addTrack($track);
        $shipment->getTracksCollection()->save();
        $shipments['usAddress'] = ['shipment' => $shipment, 'address' => $recipientAddress, 'trackNumber' => $track->getTrackNumber()];

        return $shipments;
    }
}
