<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 10.11.17
 * Time: 16:40
 */

namespace EMS\Pay\Model;

use Magento\Payment\Model\InfoInterface;

class Info extends \Magento\Payment\Model\Info implements InfoInterface
{
    const TXNTYPE = 'txntype';
    const TIMEZONE = 'timezone';
    const TXNDATETIME = 'txndatetime';
    const HASH_ALGORITHM = 'hash_algorithm';
    const HASH = 'hash';
    const STORENAME = 'storename';
    const MODE = 'mode';
    const CHARGETOTAL = 'chargetotal';
    const CHECKOUTOPTION = 'checkoutoption';
    const SHIPPING = 'shipping';
    const VATTAX = 'vattax';
    const SUBTOTAL = 'subtotal';
    const CURRENCY = 'currency';
    const ORDER_ID = 'oid';
    const TDATE = 'tdate';
    const PAYMENT_METHOD = 'paymentMethod';
    const LANGUAGE = 'language';
    const MOBILE_MODE = 'mobileMode';
    const AUTHENTICATE_TRANSACTION = 'authenticateTransaction';
    const RESPONSE_FAIL_URL = 'responseFailURL';
    const RESPONSE_SUCCESS_URL = 'responseSuccessURL';
    const TRANSACTION_NOTIFICATION_URL = 'transactionNotificationURL';
    const CHALLENGE_INDICATOR = 'threeDSRequestorChallengeIndicator';

    const APPROVAL_CODE = 'approval_code';
    const REFNUMBER = 'refnumber';
    const STATUS = 'status';
    const FAIL_REASON = 'fail_reason';
    const RESPONSE_HASH = 'response_hash';
    const NOTIFICATION_HASH = 'notification_hash';
    const PROCESSOR_RESPONSE_CODE = 'processor_response_code';
    const CC_COUNTRY = 'cccountry';
    const CC_BRAND = 'ccbrand';
    const CC_OWNER = 'bname';
    const CC_NUMBER = 'cardnumber';
    const CC_EXP_YEAR = 'expyear';
    const CC_EXP_MONTH = 'expmonth';

    const BCOMPANY = 'bcompany';
    const BNAME = 'bname';
    const BADDR1 = 'baddr1';
    const BADDR2 = 'baddr2';
    const BCITY = 'bcity';
    const BSTATE = 'bstate';
    const BCOUNTRY = 'bcountry';
    const BZIP = 'bzip';
    const BPHONE = 'phone';
    const BEMAIL = 'email';

    const SNAME = 'sname';
    const SADDR1 = 'saddr1';
    const SADDR2 = 'saddr2';
    const SCITY = 'scity';
    const SSTATE = 'sstate';
    const SCOUNTRY = 'scountry';
    const SZIP = 'szip';

    const IPG_TRANSACTION_ID = 'ipgTransactionId';
    const ENDPOINT_TRANSACTION_ID = 'endpointTransactionId';

    const KLARNA_FIRSTNAME = 'klarnaFirstname';
    const KLARNA_LASTNAME = 'klarnaLastname';
    const KLARNA_STREET = 'klarnaStreetName';
    const KLARNA_PHONE = 'klarnaPhone';

    const ACCOUNT_OWNER_NAME = 'accountOwnerName';
    const IBAN = 'iban';

    const IDEAL_ISSUER_ID = 'idealIssuerID';
    const IDEAL_CUSTOMER_ID = 'customerID';

    const BANCONTACT_ISSUER_ID = 'bancontactIssuer';

    const CART_ITEM_FIELD_INDEX = 'item';
    const CART_ITEM_FIELD_SEPARATOR = ';';
    const CART_ITEM_SHIPPING_AMOUNT = 0;
    const DISCOUNT_FIELD_NAME = 'IPG_DISCOUNT';
    const SHIPPING_FIELD_NAME = 'IPG_SHIPPING';
    const SHIPPING_FIELD_LABEL = 'IPG_SHIPPING';
    const SHIPPING_QTY = 1;

    /**
     * It's not clear yet which response field should be used as transaction ip so this constant is a placeholder
     */
    const TRANSACTION_ID = self::TDATE;

    /**
     * Payment info fields that are public (can be displayed to customer
     *
     * @var array
     */
    protected $_publicPaymentInfoFields = [
        self::ACCOUNT_OWNER_NAME
    ];

    /**
     * @var array
     */
    protected $_paymentInfoFields = [
        self::CHARGETOTAL,
        self::CURRENCY,
        self::PAYMENT_METHOD,
        self::TRANSACTION_ID,
        self::APPROVAL_CODE,
        self::REFNUMBER,
        self::IBAN,
        self::PROCESSOR_RESPONSE_CODE,
        self::IPG_TRANSACTION_ID,
        self::ENDPOINT_TRANSACTION_ID
    ];

    /**
     * @param \Magento\Payment\Model\Info $payment
     * @return array
     */
    public function getPublicPaymentInfo(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->_getPaymentInfoFields($this->_publicPaymentInfoFields, $payment);
    }

    /**
     * @param \Magento\Payment\Model\Info $payment
     * @return array
     */
    public function getPaymentInfo(\Magento\Payment\Model\InfoInterface  $payment)
    {
        return $this->_getPaymentInfoFields(array_merge($this->_paymentInfoFields, $this->_publicPaymentInfoFields), $payment);
    }

    /**
     * @param $fields
     * @param \Magento\Payment\Model\Info $payment
     * @return array
     */
    protected function _getPaymentInfoFields($fields, \Magento\Payment\Model\InfoInterface  $payment)
    {
        $info = [];
        foreach ($fields as $field)
        {
            if ($payment->hasAdditionalInformation($field)) {
                $info[$this->_getFieldLabel($field)->getText()] = $payment->getAdditionalInformation($field);
            }
        }

        return $info;
    }

    /**
     * Retrieves payment info field label
     *
     * @param string $field
     * @return string
     */
    protected function _getFieldLabel($field)
    {
        $_fieldLabels = [
            self::CHARGETOTAL => __('Amount'),
            self::CURRENCY => __('Currency'),
            self::PAYMENT_METHOD => __('Payment method'),
            self::APPROVAL_CODE => __('Approval code'),
            self::REFNUMBER => __('Reference number'),
            self::STATUS => __('Status'),
            self::TRANSACTION_ID => __('Transaction id'),
            self::ACCOUNT_OWNER_NAME => __('Account owner name'),
            self::IBAN => __('Iban'),
            self::IPG_TRANSACTION_ID => __('Ipg transaction id'),
            self::ENDPOINT_TRANSACTION_ID => __('Endpoint transaction id'),
            self::PROCESSOR_RESPONSE_CODE => __('Processor response code'),
        ];

        return isset($_fieldLabels[$field]) ? $_fieldLabels[$field] : '';
    }
}