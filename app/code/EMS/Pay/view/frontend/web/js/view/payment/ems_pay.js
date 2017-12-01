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
            emsPayCcType = 'ems_pay_cc',
            emsPaySofort = 'ems_pay_sofort',
            emsPayMaestro = 'ems_pay_maestro',
            emsPayMasterpass = 'ems_pay_masterpass',
            emsPayPayPal = 'ems_pay_paypal',
            emsPayKlar = 'ems_pay_paypal',
            emsPayBancontact = 'ems_pay_bancontact',
            emsPayIdeal = 'ems_pay_ideal';


        if (config[emsPayCcType].isActive) {
            rendererList.push(
                {
                    type: emsPayCcType,
                    component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_cc'
                }
            );
        }
        if (config[emsPaySofort].isActive) {
            rendererList.push(
                {
                    type: emsPaySofort,
                    component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_sofort'
                }
            );
        }
        if (config[emsPayMaestro].isActive) {
            rendererList.push(
                {
                    type: emsPayMaestro,
                    component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_maestro'
                }
            );
        }
        if (config[emsPayMasterpass].isActive) {
            rendererList.push(
                {
                    type: emsPayMasterpass,
                    component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_masterpass'
                }
            );
        }
        if (config[emsPayPayPal].isActive) {
            rendererList.push(
                {
                    type: emsPayPayPal,
                    component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_paypal'
                }
            );
        }
        // if (config[emsPayBancontact].isActive) {
        //     rendererList.push(
        //         {
        //             type: emsPayBancontact,
        //             component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_bancontact'
        //         }
        //     );
        // }
        if (config[emsPayIdeal].isActive) {
            rendererList.push(
                {
                    type: emsPayIdeal,
                    component: 'EMS_Pay/js/view/payment/method-renderer/ems_pay_ideal'
                }
            );
        }
        return Component.extend({});
    }
);
