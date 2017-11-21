define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'jquery'

    ],
    function (Component, customer, quote, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'EMS_Pay/payment/ems_pay_cc'
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
            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            }


        });
    }
);  
