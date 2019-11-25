=== EMS payments for Magento 2 ===
Contributors: emspay
Donate link: https://developer.emspay.eu
Tags: ems, emspay, payments, magento, e-commerce, webshop, psp, ideal, sofort, credit card, creditcard, visa, mastercard, masterpass, bancontact, bitcoin, paysafecard, direct debit, incasso, sepa, banktransfer, overboeking, betalingen, klarna
Requires at least: 2.1
Tested up to: 2.3.3
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin will add support for the following EMS payments methods to your WooCommerce webshop:

* Credit card (Visa, Mastercard, Diner's club)
* PayPal
* iDEAL
* MasterPass
* Klarna
* Sofort
* Maestro, Maestro UK

= Provisioning =

* Becoming an EMS customer

Get a merchant account by sending an email with your request to integrations@emspay.eu

= Features =

* Support for all available EMS payment methods
* Enable / disable payment methods
* Able to configure each payment method
* Toggle 3D secure transactions for the credit card payment method
* Switch between integration and production modes
* Select the pay mode of your preference (payonly, payplus, fullpay)
* Toggle payment method icons
* Transaction logs / notes in order
* IPN handling

* Contact EMS Support

Visit the FAQ:
https://developer.emspay.eu/faq

Contact information:
https://developer.emspay.eu/contact

== Screenshots ==

1. Checkout page: EMS payment methods

== Installation ==

= Minimum Requirements =

* PHP version 5.6 or greater
* PHP extensions enabled: cURL, JSON

= Updating =

Automatic updates should work flawlessly; as always though, ensure you backup your site just in case.

= 1.0.0 =
* Initial release
 
= 1.0.1 =
* added new banks ideal handelsbanken and moneyou
 
= 1.0.2 =
* added new 3d secure attribute threeDSRequestorChallengeIndicator
 
= 1.0.3 =
* added parameter authenticate transaction
 
= 1.0.4 =
* fixed change of order status in the processing

= 1.0.5 =
* fixed timezone txndatetime 
= 1.0.6 =
* updated version in README.md
 
= 1.0.7 =
* Add Order and Invoice confirmation email sending
