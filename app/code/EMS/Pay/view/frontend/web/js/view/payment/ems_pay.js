define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'ems_pay_cc',
                component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_cc'
            }
        );
        return Component.extend({});
    }
);
