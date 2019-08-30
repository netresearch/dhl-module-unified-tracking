<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model\Config;

use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleConfigTests
 *
 * @package Dhl\GroupTracking\Test\Integration
 * @author Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link   https://www.netresearch.de/
 */
class ModuleConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var mixed
     */
    private $config;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
        /** @var ModuleConfig $config */
        $this->config = $this->objectManager->create(ModuleConfig::class);
    }
    /**
     * @test
     * @magentoConfigFixture default/dhlshippingsolutions/tracking/consumer_key foo
     */
    public function testGetConsumerKey()
    {
         $result = $this->config->getConsumerKey();
        self::assertSame('foo', $result);
    }
    /**
     * @test
     * @magentoConfigFixture current_store shipping/origin/country_id DE
     */
    public function testGetShippingOriginCountry()
    {
        $countryCode = $this->config->getShippingOriginCountry();
        self::assertSame('DE', $countryCode);
    }
}
