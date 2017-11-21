<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 10.11.17
 * Time: 11:27
 */

namespace EMS\Pay\Model\Source\Klarna;



class Country
{
    protected $_currency;
    protected $_new_country;
    protected $_options;
    /**
     * @var \EMS\Pay\Gateway\Config\Config
     */
    private $_config;

    /**
     * Country constructor.
     * @param \Magento\Config\Model\Config\Source\Locale\Country $new_country
     * @param \EMS\Pay\Gateway\Config\Config $config
     */
    public function __construct(

        \Magento\Config\Model\Config\Source\Locale\Country $new_country,
        \EMS\Pay\Gateway\Config\Config $config
    )
    {
        $this->_new_country = $new_country;
        $this->_options = $new_country->toOptionArray();
        $this->_config = $config;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect = false)
    {
        foreach ($this->_options as $index => $optionData) {
            $value = $optionData['value'];
            if ($value !== '' && !$this->_config->isCountrySupportedByKlarna($value)) {
                unset($this->_options[$index]);
            }
        }
        return $this->_options;
    }
}