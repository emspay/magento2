<?php


namespace Magento\EMS\Pay\Model\Method;


class Mapper
{
    const EMS_CODE_MASTER_CARD = 'M';
    const EMS_CODE_VISA = 'V';
    const EMS_CODE_AMERICAN_EXPRESS = 'A';
    const EMS_CODE_DINERS = 'C';
    const EMS_CODE_JCB = 'J';
    const EMS_CODE_IDEAL = 'ideal';
    const EMS_CODE_KLARNA = 'klarna';
    const EMS_CODE_MAESTRO = 'MA';
    const EMS_CODE_MAESTRO_UK = 'maestroUK';
    const EMS_CODE_MASTER_PASS = 'masterpass';
    const EMS_CODE_PAYPAL = 'paypal';
    const EMS_CODE_SOFORT = 'sofort';
    const EMS_CODE_BANCONTACT = 'bancontact';

    /**
     * Maps payment method codes used in magento to codes used by EMS
     *
     * @var array
     */
    protected $_magentoToEmsMap = [
        EMS_Pay_Model_Config::METHOD_MASTER_CARD => self::EMS_CODE_MASTER_CARD,
        EMS_Pay_Model_Config::METHOD_VISA => self::EMS_CODE_VISA,
        EMS_Pay_Model_Config::METHOD_AMERICAN_EXPRESS => self::EMS_CODE_AMERICAN_EXPRESS,
        EMS_Pay_Model_Config::METHOD_DINERS => self::EMS_CODE_DINERS,
        EMS_Pay_Model_Config::METHOD_JCB => self::EMS_CODE_JCB,
        EMS_Pay_Model_Config::METHOD_IDEAL => self::EMS_CODE_IDEAL,
        EMS_Pay_Model_Config::METHOD_KLARNA => self::EMS_CODE_KLARNA,
        EMS_Pay_Model_Config::METHOD_MAESTRO => self::EMS_CODE_MAESTRO,
        EMS_Pay_Model_Config::METHOD_MAESTRO_UK => self::EMS_CODE_MAESTRO_UK,
        EMS_Pay_Model_Config::METHOD_MASTER_PASS => self::EMS_CODE_MASTER_PASS,
        EMS_Pay_Model_Config::METHOD_PAYPAL => self::EMS_CODE_PAYPAL,
        EMS_Pay_Model_Config::METHOD_SOFORT => self::EMS_CODE_SOFORT,
        EMS_Pay_Model_Config::METHOD_BANCONTACT => self::EMS_CODE_BANCONTACT,
    ];
    /**
     * Maps payment method codes used by EMS to human readable labels
     *
     * @var array
     */
    protected $_emsToLabelMap = [
        self::EMS_CODE_MASTER_CARD => 'MasterCard',
        self::EMS_CODE_VISA => 'Visa',
        self::EMS_CODE_AMERICAN_EXPRESS => 'American Express',
        self::EMS_CODE_DINERS => 'Diners',
        self::EMS_CODE_JCB => 'JCB',
        self::EMS_CODE_IDEAL => 'iDEAL',
        self::EMS_CODE_KLARNA => 'Klarna',
        self::EMS_CODE_MAESTRO => 'Maestro',
        self::EMS_CODE_MAESTRO_UK => 'Maestro UK',
        self::EMS_CODE_MASTER_PASS => 'MasterPass',
        self::EMS_CODE_PAYPAL => 'PayPal',
        self::EMS_CODE_SOFORT => 'SOFORT Banking',
        self::EMS_CODE_BANCONTACT => 'Bancontact'
    ];
    /**
     * Returns payment method code required by ems
     *
     * @param string $magentoCode Payment method code used in magento
     * @return string
     */
    public function getEmsCodeByMagentoCode($magentoCode)
    {
        $magentoCode = (string)$magentoCode;
        return isset($this->_magentoToEmsMap[$magentoCode]) ? $this->_magentoToEmsMap[$magentoCode] : '';
    }
    /**
     * Returns human readable payment method label by ems code
     *
     * @param string $emsCode Payment method code used by ems
     * @return string
     */
    public function getHumanReadableByEmsCode($emsCode)
    {
        $emsCode = (string)$emsCode;
        return isset($this->_emsToLabelMap[$emsCode]) ? $this->_emsToLabelMap[$emsCode] : '';
    }
    /**
     * Returns human readable payment method label by magento code
     *
     * @param string $magentoCode Payment method code used by magento
     * @return string
     */
    public function getHumanReadableByMagentoCode($magentoCode)
    {
        $magentoCode = (string)$magentoCode;
        return $this->getHumanReadableByEmsCode($this->getEmsCodeByMagentoCode($magentoCode));
    }
}