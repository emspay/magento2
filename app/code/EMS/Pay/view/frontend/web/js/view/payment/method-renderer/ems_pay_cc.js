define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'Magento_Checkout/js/action/place-order',
        'jquery'

    ],
    function (Component, customer, quote, url, placeOrderAction, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'EMS_Pay/payment/ems_pay_cc',
                selectedCardType: '',
                redirectAfterPlaceOrder: false
            },
            initObservable: function () {
                this._super()
                    .observe('selectedCardType');
                return this;
            },

            getTransportName: function() {
                return window.checkoutConfig.payment.paypalBillingAgreement.transportName;
            },
            /**
             * @returns {String}
             */
            getCode: function () {
                return 'ems_pay_cc';
            },

            getAvailableCardTypes: function () {
                return window.checkoutConfig.payment['ems_pay_cc'].availableCardTypes;
            },

            getLogoFileNames: function () {
                return window.checkoutConfig.payment['ems_pay_cc'].logoFileNames;
            },

            getCardTypeFieldName: function () {
                return window.checkoutConfig.payment['ems_pay_cc'].cardTypeFieldName;
            },

            getCardList: function() {
                return _.map(this.getAvailableCardTypes(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getLogoList: function() {
                return _.map(this.getLogoFileNames(), function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },

            getLogos: function (type) {
                return window.checkoutConfig.payment['ems_pay_cc'].logoFileNames.hasOwnProperty(type)
                    ? window.checkoutConfig.payment['ems_pay_cc'].logoFileNames[type]
                    : false
            },

            getData: function() {

                var additionalData = null;
                if (this.getCardTypeFieldName()) {
                    additionalData = {};
                    additionalData[this.getCardTypeFieldName()] = this.selectedCardType();
                }
                return {'method': this.getCode(), 'additional_data': additionalData};
            },
            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return window.checkoutConfig.payment['ems_pay_cc'].isActive;
            },

            afterPlaceOrder: function (data, event) {
                window.location.replace(url.build('EMS/Pa/controller'));

            }



        });
    }
);  
