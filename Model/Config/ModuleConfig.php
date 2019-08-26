<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ModuleConfig
 *
 * @package Dhl\GroupTracking\Model
 * @author Muhammad Qasim <muhammad.qasim@netresearch.de>
 * @link   https://www.netresearch.de/
 */
class ModuleConfig
{
    const CONFIG_PATH_CONSUMER_KEY = 'dhlshippingsolutions/tracking/consumer_key';

    /**
     * ModuleConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getConsumerKey(): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CONSUMER_KEY);
    }
}
