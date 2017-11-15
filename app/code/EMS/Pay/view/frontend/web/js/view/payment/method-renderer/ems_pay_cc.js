define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
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
