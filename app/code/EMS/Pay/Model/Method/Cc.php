<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 14.11.17
 * Time: 13:19
 */

namespace EMS\Pay\Model\Method;

use EMS\Pay\Gateway\Config\Config;
//use EMS\Pay\Model\Method\Cc\AbstractMethod;
use EMS\Pay\Model\Method\Cc\AbstractMethodCc;

class Cc extends AbstractMethodCc
{
    protected $_code = Config::METHOD_CC;
    protected $_formBlockType = 'ems_pay/payment_form_cc';


    /**
     * Name of field used in form
     *
     * @var string
     */
    protected $_cardTypeFieldName = 'ems_card_type';
    /**
     * @inheritdoc
     */
    protected function _is3DSecureEnabled()
    {
        return $this->_getConfig()->isCreditCard3DSecureEnabled();
    }
    /**
     * @param string $code
     * @return bool
     */
    protected function _validateCardType($code)
    {
        return !$this->_getConfig()->isCreditCardTypeEnabled($code);
    }
    /**
     * Returns list of enabled credit card types
     *
     * @return array card names indexed by card code
     */
    protected function _getEnabledCardTypes()
    {
        return $this->_getConfig()->getEnabledCreditCardTypes();
    }
}