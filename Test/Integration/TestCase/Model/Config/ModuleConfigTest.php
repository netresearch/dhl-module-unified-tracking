<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Config;

use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ModuleConfigTest
 *
 * @author Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link   https://www.netresearch.de/
 *
 * @magentoAppArea adminhtml
 */
class ModuleConfigTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ModuleConfig
     */
    private $config;

    /**
     * Init object manager and test subject
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->config = $this->objectManager->create(ModuleConfig::class);
    }

    /**
     * @test
     * @magentoConfigFixture default/dhlshippingsolutions/tracking/consumer_key foo
     */
    public function getConsumerKey()
    {
        $consumerKey = $this->config->getConsumerKey();
        self::assertSame('foo', $consumerKey);
    }

    /**
     * @test
     * @magentoConfigFixture current_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/country_id DE
     */
    public function getShippingOriginCountry()
    {
        $countryCode = $this->config->getShippingOriginCountry('admin');
        self::assertSame('US', $countryCode);

        $countryCode = $this->config->getShippingOriginCountry('default');
        self::assertSame('DE', $countryCode);
    }
}
