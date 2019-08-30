<?php
/**
 * See LICENSE.md for license details.
 */
declare(strict_types=1);

namespace Dhl\GroupTracking\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

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
    const SHIPPING_COUNTRY_CODE = 'shipping/origin/country_id';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ModuleConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int|string|null $storeId
     * @return mixed
     */
    public function getShippingOriginCountry($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SHIPPING_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function getConsumerKey(): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CONSUMER_KEY);
    }
}
