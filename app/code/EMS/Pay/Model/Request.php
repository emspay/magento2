<?php
///**
// * Created by PhpStorm.
// * User: dev01
// * Date: 24.11.17
// * Time: 15:44
// */
//
//namespace EMS\Pay\Model;
//
//
//class Request
//{
//
//    /**
//     * Set EMS_Pay data to request.
//     *
//     * @param \EMS\Pay\Model\Request $paymentMethod
//     * @return $this
//     */
//    public function setConstantData(\EMS\Pay\Model\Method\EmsAbstractMethod $paymentMethod)
//    {
//        $this->setGatewayUrl($paymentMethod->getGatewayUrl());
//
//        return $this;
//    }
//
//    /**
//     * @param \Magento\Sales\Model\Order $order
//     * @param \Magento\Authorizenet\Model\Directpost $paymentMethod
//     * @return array
//     * @throws \Exception
//     */
//    public function getRedirectFormFields(
//        \Magento\Sales\Model\Order $order,
//        \Magento\Authorizenet\Model\Directpost $paymentMethod
//    )
//    {
//        $debugData = [];
//        $config = $paymentMethod->getC_config;
//
//        $payment = $order->getPayment();
//
//        try {
//            $fields = [
//                \EMS\Pay\Model\Info::TXNTYPE => $config->getTxnType(),
//                \EMS\Pay\Model\Info::TIMEZONE => $this->_getTimezone(),
//                \EMS\Pay\Model\Info::TXNDATETIME => $this->_getTransactionTime(),
//                \EMS\Pay\Model\Info::HASH_ALGORITHM => $this->_getHashAlgorithm(),
//                \EMS\Pay\Model\Info::HASH => $this->_getHash(),
//                \EMS\Pay\Model\Info::STORENAME => $this->_getStoreName(),
//                \EMS\Pay\Model\Info::MODE => $config->getDataCaptureMode(),
//                \EMS\Pay\Model\Info::CHECKOUTOPTION => $this->_getCheckoutOption(),
//                \EMS\Pay\Model\Info::CHARGETOTAL => $this->_getChargeTotal(),
//                \EMS\Pay\Model\Info::CURRENCY => $this->_getOrderCurrencyCode(),
//                \EMS\Pay\Model\Info::ORDER_ID => $this->_getOrderId(),
//                \EMS\Pay\Model\Info::PAYMENT_METHOD => $this->_getPaymentMethod(),
//                \EMS\Pay\Model\Info::RESPONSE_FAIL_URL => $this->_store->getUrl('emspay/index/fail', array('_secure' => true)),
//                \EMS\Pay\Model\Info::RESPONSE_SUCCESS_URL => $this->_store->getUrl('emspay/index/success', array('_secure' => true)),
//                \EMS\Pay\Model\Info::TRANSACTION_NOTIFICATION_URL => $this->_store->getUrl('emspay/index/ipn', array('_secure' => true)),
//                \EMS\Pay\Model\Info::LANGUAGE => $this->_getLanguage(),
//                \EMS\Pay\Model\Info::BEMAIL => $this->_getOrder()->getCustomerEmail(),
//                \EMS\Pay\Model\Info::MOBILE_MODE => $this->_getMobileMode(),
//            ];
//
//            $fields = array_merge($fields, $this->_getAddressRequestFields());
//            $fields = array_merge($fields, $this->_getMethodSpecificRequestFields());
//            $this->_saveTransactionData();
//        } catch (\Exception $ex) {
//            $debugData['exception'] = $ex->getMessage() . ' in ' . $ex->getFile() . ':' . $ex->getLine();
//            $this->_debug($debugData);
//            throw $ex;
//        }
//
//        $debugData[] = __('Generated redirect form fields');
//        $debugData['redirect_form_fields'] = $fields;
//        $this->_debug($debugData);
//
//        return $fields;
//    }
//}