<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model\Config;

use Magento\TestFramework\ObjectManager;

/**
 * Class ModuleConfigTest
 *
 * @package Dhl\GroupTracking\Test\Integration
 * @author Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link   https://www.netresearch.de/
 */
class ModuleConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @magentoConfigFixture default/dhlshippingsolutions/tracking/consumer_key foo
     */
    public function getConsumerKey()
    {
        $objectManager = ObjectManager::getInstance();
        $config = $objectManager->create(ModuleConfig::class);
        $result = $config->getConsumerKey();
        self::assertSame('foo', $result);
    }
}
