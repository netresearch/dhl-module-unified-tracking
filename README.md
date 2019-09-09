# Deutsche Post DHL Group Tracking Extension

The DPDHL Group tracking extension offers an integration of the [Shipment Tracking - Unified
API](https://developer.dhl/api-reference/shipment-tracking) into the Magento® 2 platform. 

## Description

This extension enables integrators to request tracking details for a given
DHL shipment. The extension does not display tracking status and tracking history
on its own. It accepts a tracking number and will return the tracking details in a
format suitable for use in Magento® 2. The UI integration needs to be realized within
a carrier module or similar.

## Requirements

* PHP >= 7.0.6
* PHP Internationalization extension

## Compatibility

* Magento 2.2.x
* Magento 2.3.x

## Installation Instructions

Install sources:

    composer require dhl/module-group-tracking

Enable module:

    ./bin/magento module:enable Dhl_UnifiedTracking
    ./bin/magento setup:upgrade

Flush cache and compile:

    ./bin/magento cache:flush
    ./bin/magento setup:di:compile

## Uninstallation

To unregister the tracking module from the application, run the following command:

    ./bin/magento module:uninstall Dhl_UnifiedTracking
    composer update

## Usage

Tracking details can be obtained from the web service by either enabling the carrier
plugin or using the tracking service directly.
 
The web service allows to filter results by DHL divisions.

### Carrier Plugin

The tracking extension comes with a plugin that can be enabled to replace a carrier's
original `\Magento\Shipping\Model\Carrier\AbstractCarrierOnline::getTrackingInfo` method.

```xml
<type name="Vendor\Module\Model\Carrier">
    <plugin name="dhlgw_get_tracking_info" type="Dhl\UnifiedTracking\Plugin\Carrier\GetTrackingDetails"/>
</type>
```

The request for tracking info will then be picked up by the tracking extension and
return a result object ready to be processed by the `Magento_Shipping` core module.

### Tracking Service

The tracking extension offers an integration point that can be used to request
tracking details from the web service: `\Dhl\UnifiedTracking\Api\TrackingInfoProviderInterface::getTrackingDetails`.

Using the tracking service directly can be useful if the result needs to be modified
before passing it to the `Magento_Shipping` core module.

```php
// \Vendor\Module\Model\Carrier::getTrackingInfo

public function getTrackingInfo($tracking)
{
    $result = $this->trackingInfoProvider->getTrackingDetails($tracking, $this->getCarrierCode());
    
    if ($result instanceof \Magento\Shipping\Model\Tracking\Result\Error) {
        // create link to portal if web service returned an error
        $statusData = [
            'tracking' => $tracking,
            'url' => 'https://trackntrace.carrier.com/?track=' . $tracking,
        ];
        $result = $this->_trackStatusFactory->create(['data' => $statusData]);
    }

    return $result;
}
```

### Limit Web Service Results

The tracking web service is able to return results from across DHL divisions.
If the integration is only meant to request tracking details from a certain carrier
(e.g. DHL Paket), then the web service results should be filtered by providing
a service name via DI configuration:

```xml
<type name="Dhl\UnifiedTracking\Webservice\Pipeline\Stage\SendRequestStage">
    <arguments>
        <argument name="serviceNames" xsi:type="array">
            <item name="fooCarrierCode" xsi:type="string">foo-service</item>
            <item name="barCarrierCode" xsi:type="string">bar-service</item>
        </argument>
    </arguments>
</type>
```

The carrier code is the identifier of the Magento® carrier that the tracking number
belongs to. Compare

* `\Magento\Sales\Api\Data\TrackInterface::getTrackNumber`
* `\Magento\Sales\Api\Data\TrackInterface::getCarrierCode`

See [API docs](https://developer.dhl/api-reference/shipment-tracking#/default/get_shipments)
for a list of available service names.

## Support

In case of questions or problems, please have a look at the
[Support Portal (FAQ)](http://dhl.support.netresearch.de/) first.

If the issue cannot be resolved, you can contact the support team via the
[Support Portal](http://dhl.support.netresearch.de/) or by sending an email
to <dhl.support@netresearch.de>.

## License

[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

## Copyright

(c) 2019 DPDHL Group
