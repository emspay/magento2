<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 14.11.17
 * Time: 13:04
 */

namespace Magento\EMS\Pay\Model\Method\Cc;

use \Magento\EMS\Pay\Model\Info;
use Magento\EMS\Pay\Model\Response;


abstract class AbstractMethod extends \Magento\EMS\Pay\Model\Method\AbstractMethod
{
    /**
     * Name of field used in form
     *
     * @var string
     */
    protected $_cardTypeFieldName = '';


    public function _construct()
    {
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    protected function _getPaymentMethod()
    {
        return $this->_getMethodCodeMapper()->getEmsCodeByMagentoCode($this->_getCardType());
    }
    /**
     * @inheritdoc
     */
    protected function _getMethodSpecificRequestFields()
    {
        $fields = parent::_getMethodSpecificRequestFields();
        $fields[Info::AUTHENTICATE_TRANSACTION] = $this->_is3DSecureEnabled() ? 'true' : 'false';
        return $fields;
    }
    /**
     * Returns card type used for payment
     *
     * @return string|null
     */
    protected function _getCardType()
    {
        return $this->getInfoInstance()->getAdditionalInformation($this->_cardTypeFieldName);
    }
    /**
     * @inheritdoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $isAvailable = parent::isAvailable($quote);
        return $isAvailable && count($this->_getEnabledCardTypes()) > 0;
    }
    /**
     * @inheritdoc
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $info = $this->getInfoInstance();
        $cardType = $data->getData($this->_cardTypeFieldName);
        if ($cardType) {
            $info->setAdditionalInformation($this->_cardTypeFieldName, $cardType);
            $info->setCcType($this->_mapper->getHumanReadableByMagentoCode($cardType));
        }
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function validate()
    {
        parent::validate();
        $errorMessage = '';
        $cardType = $this->getInfoInstance()->getAdditionalInformation($this->_cardTypeFieldName);
        if ($cardType === null || $cardType == '') {
            $errorMessage = __('Card type is a required field');
        }
        if ($this->_validateCardType($cardType)) {
            $errorMessage = __('Invalid card type selected');
        }
        if ($errorMessage !== '') {
            throw new \Exception($errorMessage);
        }
        return $this;
    }
    /**
     * @return bool
     */
    protected function _is3DSecureEnabled()
    {
        return false;
    }
    /**
     * @inheritdoc
     */
    public function addTransactionData(Response $transactionResponse)
    {
        parent::addTransactionData($transactionResponse);
        $info = $this->getInfoInstance();
        $info->setCcType($transactionResponse->getCcBrand());
        $info->setCcLast4($transactionResponse->getCcNumber());
        $info->setCcExpMonth($transactionResponse->getExpMonth());
        $info->setCcExpYear($transactionResponse->getExpYear());
        $info->setCcOwner($transactionResponse->getCcOwner());
        return $this;
    }
    /**
     * Validates whether card type code is valid
     *
     * @param string $code
     * @return bool
     */
    abstract protected function _validateCardType($code);
    /**
     * * Returns list of enabled credit card types
     *
     * @return array
     */
    abstract protected function _getEnabledCardTypes();
}