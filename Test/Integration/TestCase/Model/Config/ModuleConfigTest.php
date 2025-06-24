<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Config;

use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
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
    #[\Override]
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->config = $this->objectManager->create(ModuleConfig::class);
    }

    /**
     * @magentoConfigFixture default/dhlshippingsolutions/tracking/consumer_key foo
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function getConsumerKey()
    {
        $consumerKey = $this->config->getConsumerKey();
        self::assertSame('foo', $consumerKey);
    }

    /**
     * @magentoConfigFixture current_store shipping/origin/country_id US
     * @magentoConfigFixture default_store shipping/origin/country_id DE
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function getShippingOriginCountry()
    {
        $countryCode = $this->config->getShippingOriginCountry('admin');
        self::assertSame('US', $countryCode);

        $countryCode = $this->config->getShippingOriginCountry('default');
        self::assertSame('DE', $countryCode);
    }
}
