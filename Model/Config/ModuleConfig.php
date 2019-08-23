<?php
declare(strict_types=1);

namespace Dhl\Grouptracking\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ModuleConfig
{
    const CONFIG_PATH_CONSUMER_KEY = 'dhlshippingsolutions/tracking/consumer_key';

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConsumerKey(): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CONSUMER_KEY);
    }
}
