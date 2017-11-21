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
        var config = window.checkoutConfig.payment,
            emsPayCcType = 'ems_pay_cc';
        if (config[emsPayCcType].isActive) {
            rendererList.push(
                {
                    type: emsPayCcType,
                    component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_cc'
                }
            );
        }
        return Component.extend({});
    }
);
