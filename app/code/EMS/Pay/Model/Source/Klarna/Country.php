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

    public function __construct(

        \Magento\Config\Model\Config\Source\Locale\Country $new_country
    )
    {
        $this->_new_country = $new_country;
        $this->_options = $new_country->toOptionArray();


    }

    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect = false)
    {
        foreach ($this->_options as $index => $optionData) {
            $value = $optionData['value'];
            if ($value !== '' && !$this->_currency->isCurrencySupported($value)) {
                unset($this->_options[$index]);
            } elseif ($value !== '') {
                $this->_options[$index]['label'] = $this->_currency->getCurrencyLabel($value);
            }
        }
        return $this->_options;
    }
}