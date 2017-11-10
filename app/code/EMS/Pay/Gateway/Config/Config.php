<?php

namespace Magento\EMS\Pay\Gateway\Config;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const MODE_TEST = 'test';
    const MODE_PRODUCTION = 'production';

    const DATA_TRANSFER_PAYONLY = 'payonly';
    const DATA_TRANSFER_PAYPLUS = 'payplus';
    const DATA_TRANSFER_FULLPAY = 'fullpay';

    const CHECKOUT_OPTION_CLASSIC = 'classic';
    const CHECKOUT_OPTION_COMBINEDPAGE = 'combinedpage';

    const METHOD_CC = 'ems_pay_cc';
    const METHOD_MASTER_CARD = 'ems_pay_mastercard';
    const METHOD_VISA = 'ems_pay_visa';
    const METHOD_AMERICAN_EXPRESS = 'ems_pay_americanexpress';
    const METHOD_DINERS = 'ems_pay_diners';
    const METHOD_JCB = 'ems_pay_jcb';
    const METHOD_IDEAL = 'ems_pay_ideal';
    const METHOD_KLARNA = 'ems_pay_klarna';
    const METHOD_MAESTRO = 'ems_pay_maestro';
    const METHOD_MAESTRO_UK = 'ems_pay_maestro_uk';
    const METHOD_MASTER_PASS = 'ems_pay_masterpass';
    const METHOD_PAYPAL = 'ems_pay_paypal';
    const METHOD_SOFORT = 'ems_pay_sofort';
    const METHOD_BANCONTACT = 'ems_pay_bancontact';

    const TXNTYPE_SALE = 'sale';

    const DEFAULT_LOG_FILE = 'ems_payment';

    /**
     * date format accepted by Zend_Date::toString()
     */
    const TXNDATE_ZEND_DATE_FORMAT = 'YYYY:MM:dd-HH:mm:ss';

    const GATEWAY_URL_TEST = 'https://test.ipg-online.com/connect/gateway/processing';
    const GATEWAY_URL_PRODUCTION = 'https://www.ipg-online.com/connect/gateway/processing';

    const CONFIG_FIELD_DATA_CAPTURE_MODE = 'data_capture_mode';
    const CONFIG_FIELD_DATA_SPECIFIC_CURRENCY = 'specific_currency';

    const XML_CONFIG_OPERATION_MODE = 'payment/ems_pay_general/operation_mode';
    const XML_CONFIG_CHECKOUT_OPTION = 'payment/ems_pay_general/checkout_option';
    const XML_CONFIG_STORE_NAME_TEST = 'payment/ems_pay_general/store_name_test';
    const XML_CONFIG_STORE_NAME_PRODUCTION = 'payment/ems_pay_general/store_name_production';
    const XML_CONFIG_SHARED_SECRET_TEST = 'payment/ems_pay_general/shared_secret_test';
    const XML_CONFIG_SHARED_SECRET_PRODUCTION = 'payment/ems_pay_general/shared_secret_production';
    const XML_CONFIG_LOGGING_ENABLED = 'payment/ems_pay_general/log_enabled';
    const XML_CONFIG_IDEAL_BANK_SELECTION = 'payment/ems_pay_ideal/bank_selection_enabled';
    const XML_CONFIG_BANCONTACT_BANK_SELECTION = 'payment/ems_pay_bancontact/bank_selection_enabled';
    const XML_CONFIG_CC_TYPES = 'payment/ems_pay_cc/cctypes';
    const XML_CONFIG_CC_3DSECURE = 'payment/ems_pay_cc/enable_3dsecure';

    /**
     * Current payment method code
     * @var string
     */
    protected $_methodCode = null;

    /**
     * Current store id
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * @var EMS_Pay_Helper_Data
     */
    protected $_helper;

    /**
     * List of CC-based payment methods
     *
     * @var array
     */
    protected $_creditCardMethods = [
        self::METHOD_CC,
        self::METHOD_MASTER_CARD,
        self::METHOD_VISA,
        self::METHOD_AMERICAN_EXPRESS,
        self::METHOD_DINERS,
        self::METHOD_JCB,
        self::METHOD_MAESTRO,
        self::METHOD_MAESTRO_UK
    ];

    /**
     * List of countries supported by Klarna
     *
     * @var array ISO 3166-1 alpha-2 (dwo letter) country codes
     */
    protected $_klarnaSupportedCountries = [
        'AT', // Austria
        'DE', // Germany
        'NL', // Netherlands
        'NO', // Norway
        'DK', // Denmark
        'SE', // Sweden
    ];

    /**
     * List of countries supported by Bancontact
     *
     * @var array ISO 3166-1 alpha-2 (dwo letter) country codes
     */
    protected $_bancontactSupportedCountries = [
        'BE', // Belgium

    ];

    /**
     * List of issuing banks supported by iDEAL
     *
     * @var array
     */
    protected $_idealIssuingBanks = [
        'ABNANL2A' => 'ABN AMRO',
        'ASNBNL21' => 'ASN Bank',
        'BUNQNL2A' => 'Bunq',
        'INGBNL2A' => 'ING',
        'KNABNL2H' => 'Knab',
        'RABONL2U' => 'Rabobank',
        'RBRBNL21' => 'RegioBank',
        'SNSBNL2A' => 'SNS Bank',
        'TRIONL2U' => 'Triodos Bank',
        'FVLBNL22' => 'van Lanschot',
    ];

    /**
     * List of issuing banks supported by Bancontact
     *
     * @var array
     */
    protected $_bancontactIssuingBanks = [

        'ABERBE22' => 'ABK Bank',
        'ARSPBE22' => 'Argenta',
        'AXABBE22' => 'AXA BANK EUROPE',
        'AXBIBEBB' => 'AXA BELGIUM',
        'JVBABE22' => 'Bank J. Van Breda',
        'GKCCBEBB' => 'Belfius',
        'CTBKBEBX' => 'Beobank',
        'GEBABEBB' => 'BNP Paribas Fortis',
        'PCHQBEBB' => 'BPOST',
        'BPOTBEBE' => 'BPOST BANK-BPOST BANQUE',
        'CREGBEBB' => 'CBC Banque',
        'CPHBBE75' => 'CPH Banque',
        'NICABEBB' => 'Crelan',
        'DEUTBEBE' => 'Deutsche Bank',
        'BBRUBEBB' => 'ING België',
        'KREDBEBB' => 'KBC Bank',
        'KEYTBEBB' => 'Keytrade Bank',
        'BNAGBEBB' => 'Nagelmackers',
        'HBKABE22' => 'Record Bank',
        'VDSPBE91' => 'VDK Spaarbank',
    ];

    /**
     * List of maestro debit card types
     *
     * @var array
     */
    protected $_maestroCardTypes = [
        self::METHOD_MAESTRO => 'Maestro',
        self::METHOD_MAESTRO_UK => 'Maestro UK'
    ];

    /**
     * List of available credit card types
     *
     * @var array
     */
    protected $_creditCardTypes = [
        self::METHOD_VISA => 'Visa',
        self::METHOD_MASTER_CARD => 'MasterCard',
        self::METHOD_DINERS => 'Diners',
    ];

    /**
     * @var string
     */
    protected $_defaultLanguage = 'en_US';

    /**
     * @var array
     */
    protected $_supportedLanguages = [
        'zh_CN', // Chinese (simplified)
        'zh_TW', // Chinese (traditional)
        'nl_NL', // Dutch
        'en_US', // English (USA)
        'en_GB', // English (UK)
        'fi_FI', // Finnish
        'fr_FR', // French
        'de_DE', // German
        'it_IT', // Italian
        'pt_BR', // Portuguese (Brazil)
        'sk_SK', // Slovak
        'es_ES', // Spanish
    ];

    /**
     * Maps payment method codes to logo file names
     *
     * @var array
     */
    protected $_logosMap = [
        self::METHOD_CC => '',
        self::METHOD_JCB => '',
        self::METHOD_AMERICAN_EXPRESS => '',
        self::METHOD_MASTER_CARD => 'mastercard.png',
        self::METHOD_VISA => 'visa.png',
        self::METHOD_DINERS => 'dinersclub.png',
        self::METHOD_IDEAL => 'ideal.png',
        self::METHOD_KLARNA => 'klarna.png',
        self::METHOD_MAESTRO => 'maestro.png',
        self::METHOD_MAESTRO_UK => 'maestro.png',
        self::METHOD_MASTER_PASS => 'masterpass.png',
        self::METHOD_PAYPAL => 'paypal.png',
        self::METHOD_SOFORT => 'sofort.png',
        self::METHOD_BANCONTACT => 'bancontact.svg',
    ];

    /**
     * EMS_Pay_Model_Config constructor.
     * @param array $params first element should be payment method code, second element should be store id
     */
    public function __construct(array $params = [])
    {
        $this->_helper = Mage::helper('ems_pay');

        if ($params) {
            $this->setMethod(array_shift($params));
            if ($params) {
                $this->setStoreId(array_shift($params));
            }
        }
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getGatewayUrl($storeId = null)
    {
        return $this->_isProductionMode($storeId) ?
            self::GATEWAY_URL_PRODUCTION :
            self::GATEWAY_URL_TEST;
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getStoreName($storeId = null)
    {
        $configPath = $this->_isProductionMode($storeId) ?
            self::XML_CONFIG_STORE_NAME_PRODUCTION :
            self::XML_CONFIG_STORE_NAME_TEST;
        return Mage::getStoreConfig($configPath, $storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getSharedSecret($storeId = null)
    {
        $configPath = $this->_isProductionMode() ?
            self::XML_CONFIG_SHARED_SECRET_PRODUCTION :
            self::XML_CONFIG_SHARED_SECRET_TEST;
        return Mage::getStoreConfig($configPath, $storeId);
    }

    /**
     * @return string
     */
    public function getTxnType()
    {
        return self::TXNTYPE_SALE;
    }

    /**
     * @return string
     */
    public function getDataCaptureMode()
    {
        if ($this->getCheckoutOption() == self::CHECKOUT_OPTION_COMBINEDPAGE) {
            //combinedpage doesn't support other data capture modes
            return self::DATA_TRANSFER_PAYONLY;
        }

        return $this->getConfigData(self::CONFIG_FIELD_DATA_CAPTURE_MODE);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getCheckoutOption($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_CHECKOUT_OPTION, $storeId);
    }

    /**
     * Check whether the specified payment method is a CC-based one
     *
     * @param string $code
     * @return bool
     */
    public function isCreditCardMethod($code)
    {
        return in_array((string)$code, $this->_creditCardMethods);
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isDebuggingEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_LOGGING_ENABLED, $storeId);
    }

    /**
     * @param $storeId
     * @return bool
     */
    protected function _isProductionMode($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_OPERATION_MODE, $storeId) == self::MODE_PRODUCTION;
    }

    /**
     * Checks whether specified currency is supported for payment method
     *
     * @param string $currencyCode ISO 4217 alphanumeric currency code
     * @return bool
     */
    public function isCurrencySupported($currencyCode)
    {
        $isSupported = Mage::getModel('ems_pay/currency')->isCurrencySupported($currencyCode);
        $allowedCurrencies = $this->getConfigData(self::CONFIG_FIELD_DATA_SPECIFIC_CURRENCY);
        if ((string)$allowedCurrencies === '') {
            return $isSupported;
        }

        $allowedCurrencies = explode(',', $allowedCurrencies);

        return $isSupported && in_array($currencyCode, $allowedCurrencies);
    }

    /**
     * Checks if given country is supported by Klarna
     *
     * @param string $countryCode ISO 3166-1 alpha-2 (dwo letter) country code
     * @return bool
     */
    public function isCountrySupportedByKlarna($countryCode)
    {
        return in_array($countryCode, $this->_klarnaSupportedCountries);
    }

    /**
     * Checks if given country is supported by Bancontact
     *
     * @param string $countryCode ISO 3166-1 alpha-2 (dwo letter) country code
     * @return bool
     */
    public function isCountrySupportedByBancontact($countryCode)
    {
        return in_array($countryCode, $this->_bancontactSupportedCountries);
    }

    /**
     * @return bool
     */
    public function isIdealIssuingBankSelectionEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_IDEAL_BANK_SELECTION);
    }


    /**
     * @return bool
     */
    public function isBancontactIssuingBankSelectionEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BANCONTACT_BANK_SELECTION);
    }

    /**
     * Returns list of issuing banks supported by iDEAL
     *
     * @return array bank names indexed by bank code
     */
    public function getIdealIssuingBanks()
    {
        $banks = $this->_idealIssuingBanks;
        foreach ($banks as $code => $name) {
            $banks[$code] = $this->_helper->__($name);
        }

        return $banks;
    }

    /**
     * Returns list of issuing banks supported by Bancontact
     *
     * @return array bank names indexed by bank code
     */
    public function getBancontactIssuingBanks()
    {
        $banks = $this->_bancontactIssuingBanks;
        foreach ($banks as $code => $name) {
            $banks[$code] = $this->_helper->__($name);
        }

        return $banks;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isIdealIssuingBankCodeValid($code) {
        return isset($this->_idealIssuingBanks[strtoupper($code)]);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isBancontactIssuingBankCodeValid($code) {
        return isset($this->_bancontactIssuingBanks[strtoupper($code)]);
    }

    /**
     * Returns list of maestro debit card types
     *
     * @return array card names indexed by card code
     */
    public function getMaestroCardTypes()
    {
        $cards = $this->_maestroCardTypes;
        foreach ($cards as $code => $name) {
            $cards[$code] = $this->_helper->__($name);
        }

        return $cards;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isMaestroCardTypeCodeValid($code) {
        return isset($this->_maestroCardTypes[$code]);
    }

    /**
     * Returns list of available credit card types
     *
     * @return array card names indexed by card code
     */
    public function getAvailableCreditCardTypes()
    {
        $cards = $this->_creditCardTypes;
        foreach ($cards as $code => $name) {
            $cards[$code] = $this->_helper->__($name);
        }

        return $cards;
    }

    /**
     * Returns list of enabled credit card types
     *
     * @return array card names indexed by card code
     */
    public function getEnabledCreditCardTypes()
    {
        $availableCards = $this->getAvailableCreditCardTypes();
        $allowedCardTypes = Mage::getStoreConfig(self::XML_CONFIG_CC_TYPES);
        if ((string)$allowedCardTypes === '') {
            return [];
        }

        $allowedCardTypes = explode(',', $allowedCardTypes);

        foreach ($availableCards as $code => $name) {
            if (!in_array($code, $allowedCardTypes)) {
                unset($availableCards[$code]);
            }
        }

        return $availableCards;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isCreditCardTypeEnabled($code) {
        $enabledCardTypes = $this->getEnabledCreditCardTypes();
        return isset($enabledCardTypes[$code]);
    }

    /**
     * @return bool
     */
    public function isCreditCard3DSecureEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_CC_3DSECURE);
    }

    /**
     * Check if the device is mobile.
     * Returns true if any type of mobile device detected, including special ones
     *
     * @return bool
     */
    public function isMobileMode()
    {
        $detect = new Mobile_Detect();
        return $detect->isMobile();
    }

    /**
     * Returns current locale or the default one if current is not supported by EMS
     *
     * @return string
     */
    public function getLanguage()
    {
        $lang = Mage::app()->getLocale()->getLocaleCode();
        if (in_array($lang, $this->_supportedLanguages)) {
            return $lang;
        }

        return $this->_defaultLanguage;
    }

    /**
     * Returns payment method logo file name
     *
     * @param string $methodCode
     * @return string
     */
    public function getLogoFilename($methodCode = null)
    {
        $methodCode = ($methodCode !== null) ? (string)$methodCode : $this->_methodCode;
        return isset($this->_logosMap[$methodCode]) ? $this->_logosMap[$methodCode] : '';
    }

    /**
     * Returns log file name for current method
     *
     * @return string
     */
    public function getLogFile()
    {
        return $this->_methodCode != '' ? "payment_{$this->_methodCode}.log" : self::DEFAULT_LOG_FILE;
    }

    /**
     * @param string $field
     * @return string
     */
    public function getConfigData($field)
    {
        $path = 'payment/' . $this->_methodCode . '/' . $field;
        return Mage::getStoreConfig($path, $this->_storeId);
    }

    /**
     * @param string|Mage_Payment_Model_Method_Abstract $method
     */
    public function setMethod($method)
    {
        if ($method instanceof Mage_Payment_Model_Method_Abstract) {
            $this->_methodCode = $method->getCode();
        } elseif (is_string($method)) {
            $this->_methodCode = $method;
        }
    }

    /**
     * @param int $storeId
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int) $storeId;
    }
}
