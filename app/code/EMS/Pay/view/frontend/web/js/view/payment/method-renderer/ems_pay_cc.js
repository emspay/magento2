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
            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            }
        });
    }
);  
