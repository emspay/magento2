<?php


namespace EMS\Pay\Model;


class Currency extends \Magento\Directory\Model\Currency
{
    /**
     * Maps supported ISO 4217 alphanumeric to number codes
     *
     * @var array
     */
    protected $_currencyMap = [
        'AUD' => '036', // Australian dollar
        'BRL' => '986', // Brazilian real
        'EUR' => '978', // Euro
        'INR' => '356', // Indian rupee
        'GBP' => '826', // Pound sterling
        'USD' => '840', // United States dollar
        'ZAR' => '710', // South African rand
        'CHF' => '756', // Swiss franc
        'AWG' => '533', // Aruban florin
        'KYD' => '136', // Cayman Islands dollar
        'DOP' => '214', // Dominican peso
        'BSD' => '044', // Bahamian dollar
        'BHD' => '048', // Bahraini dinar
        'BBD' => '052', // Barbadian dollar
        'BZD' => '084', // Belize dollar
        'CAD' => '124', // Canadian dollar
        'CNY' => '156', // Chinese yuan
        'HRK' => '191', // Croatian kuna
        'CZK' => '203', // Czech koruna
        'DKK' => '208', // Danish krone
        'XCD' => '951', // East Caribbean dollar
        'GYD' => '328', // Guyanese dollar
        'HKD' => '344', // Hong Kong dollar
        'HUF' => '348', // Hungarian forint
        'ISL' => '376', // Israeli new shekel
        'JMD' => '388', // Jamaican dollar
        'JPY' => '392', // Japanese yen
        'KWD' => '414', // Kuwaiti dinar
        'LTL' => '440', // Lithuanian litas
        'MXN' => '484', // Mexican peso
        'NZD' => '554', // New Zealand dollar
        'ANG' => '532', // Netherlands Antillean guilder
        'NOK' => '578', // Norwegian krone
        'OMR' => '512', // Omani rial
        'PLN' => '985', // Polish zloty
        'RON' => '946', // Romanian leu
        'SAR' => '682', // Saudi riyal
        'SGD' => '702', // Singapore dollar
        'KRW' => '410', // South Korean won
        'SRD' => '968', // Surinamese dollar
        'SEK' => '752', // Swedish krona
        'TTD' => '780', // Trinidad and Tobago dollar
        'TRY' => '949', // Turkish lira
        'AED' => '784', // United Arab Emirates dirham
    ];
    /**
     * Maps supported ISO 4217 alphanumeric currency codes to labels
     *
     * @var array
     */
    protected $_currencyLabels = [
        'AUD' => 'Australian dollar',
        'BRL' => 'Brazilian real',
        'EUR' => 'Euro',
        'INR' => 'Indian rupee',
        'GBP' => 'Pound sterling',
        'USD' => 'United States dollar',
        'ZAR' => 'South African rand',
        'CHF' => 'Swiss franc',
        'AWG' => 'Aruban florin',
        'KYD' => 'Cayman Islands dollar',
        'DOP' => 'Dominican peso',
        'BSD' => 'Bahamian dollar',
        'BHD' => 'Bahraini dinar',
        'BBD' => 'Barbadian dollar',
        'BZD' => 'Belize dollar',
        'CAD' => 'Canadian dollar',
        'CNY' => 'Chinese yuan',
        'HRK' => 'Croatian kuna',
        'CZK' => 'Czech koruna',
        'DKK' => 'Danish krone',
        'XCD' => 'East Caribbean dollar',
        'GYD' => 'Guyanese dollar',
        'HKD' => 'Hong Kong dollar',
        'HUF' => 'Hungarian forint',
        'ISL' => 'Israeli new shekel',
        'JMD' => 'Jamaican dollar',
        'JPY' => 'Japanese yen',
        'KWD' => 'Kuwaiti dinar',
        'LTL' => 'Lithuanian litas',
        'MXN' => 'Mexican peso',
        'NZD' => 'New Zealand dollar',
        'ANG' => 'Netherlands Antillean guilder',
        'NOK' => 'Norwegian krone',
        'OMR' => 'Omani rial',
        'PLN' => 'Polish zloty',
        'RON' => 'Romanian leu',
        'SAR' => 'Saudi riyal',
        'SGD' => 'Singapore dollar',
        'KRW' => 'South Korean won',
        'SRD' => 'Surinamese dollar',
        'SEK' => 'Swedish krona',
        'TTD' => 'Trinidad and Tobago dollar',
        'TRY' => 'Turkish lira',
        'AED' => 'United Arab Emirates dirham'
    ];
    /**
     * @var array
     */
    protected $_klarnaSupportedCountryCurrency = [
        'EUR' => ['AT', 'DE', 'NL', 'NO'], //Euro (978)
        'DKK' => ['DK'], //Danish krone (208)
        'NOK' => ['NO'], //Norwegian krone (578)
        'SEK' => ['SE'], //Swedish krona (752)
    ];
    /**
     * @var array
     */
    protected $_idealSupportedCurrencies = [
        'EUR' => '978', // Euro
    ];
    protected $_bancontactSupportedCurrencies = [
        'EUR' => '978', // Euro
    ];

    /**
     * @param string|\Magento\Directory\Model\Currency $currency code or object
     * @return int
     * @throws \Exception
     */
    public function getNumericCurrencyCode($currency)
    {
        $code = $currency instanceof \Magento\Directory\Model\Currency ?
            $currency->getCode() :
            (string)$currency;
        if ($code == '') {
            throw new \Exception(__('Currency code can not be empty'));
        }
        if (!isset($this->_currencyMap[$code])) {
            throw new \Exception(__('%s currency is not allowed', $code));
        }
        return $this->_currencyMap[$code];
    }
    /**
     * @param $numericCode
     * @return string
     */
    public function getTextCurrencyCode($numericCode)
    {
        $res = array_search($numericCode, $this->_currencyMap);
        return $res !== false ? $res : '';
    }
    /**
     * Returns label for given currency code
     *
     * @param string $currencyCode ISO 4217 alphanumeric currency code
     * @return string
     */
    public function getCurrencyLabel($currencyCode)
    {
        $label = '';
        if (isset($this->_currencyLabels[$currencyCode])) {
            $label = __($this->_currencyLabels[$currencyCode]);
        }
        return $label;
    }
    /**
     * @param string $currencyCode ISO 4217 alphanumeric currency code
     * @return bool
     */
    public function isCurrencySupported($currencyCode)
    {
        return isset($this->_currencyMap[$currencyCode]);
    }
    /**
     * Checks whether given currency is supported by Klarna.
     *
     * @param string $currencyCode ISO 4217 alphanumeric currency code
     * @param string $countryCode ISO 3166-1 alpha-2 (dwo letter) country code
     * @return bool
     */
    public function isCurrencySupportedByKlarna($currencyCode, $countryCode = '')
    {
        if ($countryCode === '') {
            return isset($this->_klarnaSupportedCountryCurrency[$currencyCode]);
        }
        return isset($this->_klarnaSupportedCountryCurrency[$currencyCode])
        && in_array($countryCode, $this->_klarnaSupportedCountryCurrency[$currencyCode]);
    }
    /**
     * Checks whether given currency is supported by iDEAL.
     *
     * @param string $currencyCode ISO 4217 alphanumeric currency code
     * @return bool
     */
    public function isCurrencySupportedByIdeal($currencyCode)
    {
        return $this->isCurrencySupportedByKlarna($currencyCode) && isset($this->_idealSupportedCurrencies[$currencyCode]);
    }
    /**
     * Checks whether given currency is supported by Bancontact.
     *
     * @param string $currencyCode ISO 4217 alphanumeric currency code
     * @param string $countryCode ISO 3166-1 alpha-2 (dwo letter) country code
     * @return bool
     */
    public function isCurrencySupportedByBancontact($currencyCode, $countryCode = '')
    {
        if ($countryCode === '') {
            return isset($this->_bancontactSupportedCurrencies[$currencyCode]);
        }
        return isset($this->_bancontactSupportedCurrencies[$currencyCode])
        && in_array($countryCode, $this->_bancontactSupportedCurrencies[$currencyCode]);
    }
}