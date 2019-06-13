# EMS payments for Magento
Accept payments in Magento with the official EMS e-Commerce gateway plugin.

## Description
This plugin will add support for the following EMS payments methods to your Magento 2 webshop:

* Credit card (Visa, Mastercard, Diner's club)
* PayPal
* iDEAL
* MasterPass
* Klarna
* Sofort
* Bancontact
* Maestro, Maestro UK

## Provisioning

### Are you already a customer ?
If you are already registered as an EMS merchant then please enter the credentials and settings below.

For new customers please follow the link below to acquire an EMS merchant account.

### Becoming an EMS customer
Get a merchant account by sending an email with your request to sales@emspay.eu

### Contact EMS Support
Visit the FAQ:
https://emspay.eu/get-in-touch

Contact information:
https://emspay.eu/contact

## Features
* Support for all available EMS payment methods
* Enable / disable payment methods
* Able to configure each payment method
* Toggle 3D secure transactions for the credit card payment method
* Switch between integration and production modes
* Select the pay mode of your preference (payonly, payplus, fullpay)
* Toggle payment method icons
* Transaction logs / notes in order
* IPN handling

## Installation

* Compatible with Magento 2.0.0 - 2.2.4

    ##### It's highly recommended to test the module on dev/staging environment before installing it on production and to backup you site's code and database before installing the module.

    ##### 1. Backup the website code and database
    You can use Magento backup function or any other tool you like

    ##### 2. Copy/upload the module files into your site's root directory
        
    ##### 3. To enable the extension run commands: 
    ```
    php bin/magento module:enable EMS_Pay
    php bin/magento setup:upgrade
    ```
        
    ##### 4. Clean the cache
    ```
    php bin/magento cache:clean
    ```
        
## Configuration

##### General Configuration
1. Log in to magento admin panel
2. Navigate to **Stores / Configuration / Sales / Payment Methods / EMS Global Configuration** 
3. Choose operation mode
4. Enter Store name and Shared secret for chosen operation mode
5. Choose Checkout option

##### Configuration for individual payment methods
Review configuration options for individual payment methods and adjust it to meet your preferences