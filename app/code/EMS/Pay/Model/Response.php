<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 10.11.17
 * Time: 17:20
 */

namespace EMS\Pay\Model;

use \EMS\Pay\Model\Method\EmsAbstractMethod;
use Magento\Directory\Model\CurrencyFactory;

class Response
{
    const FIELD_REFNUMBER = Info::REFNUMBER;
    const FIELD_RESPONSE_HASH = Info::RESPONSE_HASH;
    const FIELD_APPROVAL_CODE = Info::APPROVAL_CODE;
    const FIELD_NOTIFICATION_HASH = Info::NOTIFICATION_HASH;
    const FIELD_ORDER_ID = Info::ORDER_ID;
    const FIELD_CURRENCY = Info::CURRENCY;
    const FIELD_CHARGETOTAL = Info::CHARGETOTAL;
    const FIELD_PAYMENT_METHOD = Info::PAYMENT_METHOD;

    const FIELD_FAIL_REASON = Info::FAIL_REASON;
    const FIELD_TRANSACTION_ID = Info::TRANSACTION_ID;
    const FIELD_IPG_TRANSACTION_ID = Info::IPG_TRANSACTION_ID;
    const FIELD_ENDPOINT_TRANSACTION_ID = Info::ENDPOINT_TRANSACTION_ID;
    const FIELD_PROCESSOR_RESPONSE_CODE = Info::PROCESSOR_RESPONSE_CODE;

    const FIELD_CC_EXP_YEAR = Info::CC_EXP_YEAR;
    const FIELD_CC_EXP_MONTH = Info::CC_EXP_MONTH;
    const FIELD_CC_BRAND = Info::CC_BRAND;
    const FIELD_CC_OWNER = Info::CC_OWNER;
    const FIELD_CC_NUMBER = Info::CC_NUMBER;

    const FIELD_IBAN = Info::IBAN;
    const FIELD_ACCOUNT_OWNER_NAME = Info::ACCOUNT_OWNER_NAME;

    const APPROVAL_CODE_SUCCESS = 'Y';
    const APPROVAL_CODE_FAILURE = 'N';
    const APPROVAL_CODE_WAITING = '?';

    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILURE = 'FAILURE';
    const STATUS_WAITING = 'WAITING';

    /**
     * @var array
     */
    protected $_response = null;

    /**
     * @var Hash
     */
    protected $_hashHandler;

    protected $_currency;


    public function __construct(
       CurrencyFactory $currencyFactory,
       Hash $hash,
       array $response

       
    )
    {
        $this->_currencyFactory = $currencyFactory;
        $this->_hashHandler = $hash;
        $this->_response = $response;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->_response[self::FIELD_ORDER_ID];
    }

    /**
     * @return string
     */
    public function getTransactionStatus()
    {
        $code = strtoupper(substr($this->_response[self::FIELD_APPROVAL_CODE], 0, 1));
        $status = '';
        switch ($code) {
            case self::APPROVAL_CODE_SUCCESS:
                $status = self::STATUS_SUCCESS;
                break;
            case self::APPROVAL_CODE_WAITING:
                $status = self::STATUS_WAITING;
                break;
            case self::APPROVAL_CODE_FAILURE:
                $status = self::STATUS_FAILURE;
                break;
        }

        return $status;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_response[self::FIELD_TRANSACTION_ID];
    }

    /**
     * @return string
     */
    public function getTextCurrencyCode()
    {
        if(null === $this->_currency) {
            $this->_currency = $this->_currencyFactory->create();
        }
        return $this->_currency->getTextCurrencyCode($this->_response[self::FIELD_CURRENCY]);
    }

    /**
     * @return string
     */
    public function getChargeTotal()
    {
        return $this->_response[self::FIELD_CHARGETOTAL];
    }

    /**
     * @return string
     */
    public function getCcBrand() {
        return $this->_getField(self::FIELD_CC_BRAND);
    }

    /**
     * @return string
     */
    public function getCcNumber()
    {
        return $this->_getField(self::FIELD_CC_NUMBER);
    }

    /**
     * @return string
     */
    public function getCcOwner()
    {
        return $this->_getField(self::FIELD_CC_OWNER);
    }

    /**
     * @return string
     */
    public function getExpMonth()
    {
        return $this->_getField(self::FIELD_CC_EXP_MONTH);
    }

    /**
     * @return string
     */
    public function getExpYear()
    {
        return $this->_getField(self::FIELD_CC_EXP_YEAR);
    }

    /**
     * @return string
     */
    public function getFailReason()
    {
        return $this->_getField(self::FIELD_FAIL_REASON);
    }

    /**
     * @return string
     */
    public function getAccountOwnerName()
    {
        return $this->_getField(self::FIELD_ACCOUNT_OWNER_NAME);
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->_getField(self::FIELD_IBAN);
    }

    /**
     * @return string
     */
    public function getIpgTransactionId()
    {
        return $this->_getField(self::FIELD_IPG_TRANSACTION_ID);
    }

    /**
     * @return string
     */
    public function getEndpointTransactionId()
    {
        return $this->_getField(self::FIELD_ENDPOINT_TRANSACTION_ID);
    }

    /**
     * @return string
     */
    public function getProcessorResponseCode()
    {
        return $this->_getField(self::FIELD_PROCESSOR_RESPONSE_CODE);
    }

    /**
     * @return string
     */
    public function getApprovalCode()
    {
        return $this->_getField(self::FIELD_APPROVAL_CODE);
    }

    /**
     * string
     */
    public function getRefNumber()
    {
        return $this->_getField(self::FIELD_REFNUMBER);
    }

    /**
     * @param EmsAbstractMethod $payment
     * @return bool
     */
    public function validate(EmsAbstractMethod $payment)
    {
        $this->_validateRequiredFields();
        $this->_validateHash($payment);

        return true;
    }

    /**
     * @param EmsAbstractMethod $payment
     * @return bool
     */
    protected function _validateHash(EmsAbstractMethod $payment)
    {
        return $this->_isNotification() ? $this->_validateNotificationHash($payment) : $this->_validateResponseHash($payment);
    }

    /**
     * @param EmsAbstractMethod $payment
     * @return bool
     * @throws \Exception
     */
    protected function _validateResponseHash(EmsAbstractMethod $payment)
    {
        $hash = $this->_hashHandler->generateResponseHash(
            $payment->getHashAlgorithmSentInTransactionRequest(),
            $payment->getTransactionTimeSentInTransactionRequest(),
            $this->_response[self::FIELD_CHARGETOTAL],
            $this->_response[self::FIELD_CURRENCY],
            $this->_response[self::FIELD_APPROVAL_CODE]
        );

        if ($this->_response[self::FIELD_RESPONSE_HASH] !== $hash) {
            throw new \Exception(__('Response hash is not valid'));
        }

        return true;
    }

    /**
     * @param AbstractMethod $payment
     * @return bool
     * @throws \Exception
     */
    protected function _validateNotificationHash(EmsAbstractMethod $payment)
    {
        $hash = $this->_hashHandler->generateNotificationHash(
            $payment->getHashAlgorithmSentInTransactionRequest(),
            $payment->getTransactionTimeSentInTransactionRequest(),
            $this->_response[self::FIELD_CHARGETOTAL],
            $this->_response[self::FIELD_CURRENCY],
            $this->_response[self::FIELD_APPROVAL_CODE]
        );

        if ($this->_response[self::FIELD_NOTIFICATION_HASH] !== $hash) {
            throw new \Exception(__('Notification hash is not valid'));
        }

        return true;
    }

    /**
     * Checks if notification request contains all fields that are required
     *
     * @return bool
     * @throws \Exception
     */
    protected function _validateRequiredFields()
    {
        $requiredFields = [
            self::FIELD_ORDER_ID,
            self::FIELD_CURRENCY,
            self::FIELD_CHARGETOTAL,
            self::FIELD_APPROVAL_CODE,
        ];

        $requiredFields[] = $this->_isNotification() ? self::FIELD_NOTIFICATION_HASH : self::FIELD_RESPONSE_HASH;

        foreach ($requiredFields as $field) {
            if (!$this->_isRequestFieldNotEmpty($field)) {
                throw new \Exception(__("%s missing in notification request", $field));
            }
        }

        return true;
    }

    /**
     * Retrieves single field value from response
     *
     * @param string $field
     * @return string
     */
    protected function _getField($field)
    {
        return isset($this->_response[$field]) ? $this->_response[$field] : '';
    }

    /**
     * @return bool
     */
    protected function _isNotification()
    {
        return isset($this->_response[self::FIELD_NOTIFICATION_HASH]);
    }

    /**
     * Checks if single notification request field is not empty
     *
     * @param string $fieldIndex
     * @return bool
     */
    protected function _isRequestFieldNotEmpty($fieldIndex)
    {
        return isset($this->_response[$fieldIndex]) && (string)$this->_response[$fieldIndex] !== '';
    }
}