<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Dhl\UnifiedTracking\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order\Shipment;

class ModuleConfig
{
    private const CONFIG_PATH_CONSUMER_KEY = 'dhlshippingsolutions/tracking/consumer_key';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ModuleConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Read origin country from shipping configuration.
     *
     * @param int|string|null $store
     * @return string
     */
    public function getShippingOriginCountry($store = null): string
    {
        return $this->scopeConfig->getValue(Shipment::XML_PATH_STORE_COUNTRY_ID, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Read consumer key for API authentication.
     *
     * @return string
     */
    public function getConsumerKey(): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CONSUMER_KEY);
    }
}
