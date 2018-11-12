Becopay payment gateway for Magento 2
====================

Version: 1.0.0

Tags: online payment, payment, payment gateway, becopay, magento2

Requires at least: 2.0.0

Tested up to: 2.2.6

License: [Apache 2.0](https://opensource.org/licenses/Apache-2.0)


## Prerequisites


You must have a Becopay merchant account to use this plugin.  It's free and easy to [sign-up for a becopay merchant account](https://becopay.com/en/merchant-register/).


## How to install
Before you begin, make sure that you have installed Composer. In your terminal, go to the Magento folder and run the following commands:
```
bin/magento maintenance:enable
composer clear-cache
composer require becopay/magento2-becopay-gateway:*
bin/magento setup:upgrade
rm -rf var/di var/generation generated/code && bin/magento setup:di:compile
rm -rf pub/static/* && bin/magento setup:static-content:deploy en_US <additional locales, e.g.: de_DE>
bin/magento maintenance:disable
```

## How to update
```
bin/magento maintenance:enable
composer clear-cache
composer update becopay/magento2-becopay-gateway
bin/magento setup:upgrade
rm -rf var/di var/generation generated/code && bin/magento setup:di:compile
rm -rf pub/static/* && bin/magento setup:static-content:deploy en_US <additional locales, e.g.: de_DE>
bin/magento maintenance:disable
```
## Configure the plugin in Magento 

Before you begin, make sure that you have set up your Becopay payment gateway.

Configure the Becopay plugin in your Magento admin panel: 

1. Log in to your Magento admin panel. 
2. In the left navigation bar, go to ``Stores > Configuration.``
3. In the menu, go to ``Sales > Payment Methods.``
4. Click Required Settings and fill out the following fields: 

* __Enable/Disable__ - Select ``Enable`` to enable Becopay Payment Gateway.
* __Title__ - Allows you to determine what your customers will see this payment option as on the checkout page.
* __Mobile__  - Enter the phone number you registered in the Becopay here.If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
* __Api Base Url__  - Enter Becopay api base url here. If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
* __Merchant Api Key__  - Enter your Becopay Api Key here. If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
* __Order Status after payment__ - Status given to orders after the payment has been completed
* __Payment From Applicable Countries__ - Payment options limited to specific countries
* __Sort Order__ - Add Gateway list sort number
* Click on __Save Config__ For the changes you made to be effected.

### Plugin Callback URL
```https://your-site/becopay/payment/callback/?orderId=```

## Becopay Support:

* [GitHub Issues](https://github.com/becopay/Magento2-Becopay-Gateway/issues)
  * Open an issue if you are having issues with this plugin
* [Support](https://becopay.com/en/support/#contact-us)
  * Becopay support
* [Documentation](https://becopay.com/en/io#api)
  * Technical documentation

## Contribute

Would you like to help with this project?  Great!  You don't have to be a developer, either.  If you've found a bug or have an idea for an improvement, please open an [issue](https://github.com/becopay/Magento2-Becopay-Gateway/issues) and tell us about it.

If you *are* a developer wanting contribute an enhancement, bug fix or other patch to this project, please fork this repository and submit a pull request detailing your changes. We review all PRs!

This open source project is released under the [Apache 2.0 license](https://opensource.org/licenses/Apache-2.0) which means if you would like to use this project's code in your own project you are free to do so.  Speaking of, if you have used our code in a cool new project we would like to hear about it!  [Please send us an email](mailto:io@becopay.com).

## License

Please refer to the [LICENSE](https://opensource.org/licenses/Apache-2.0) file that came with this project.