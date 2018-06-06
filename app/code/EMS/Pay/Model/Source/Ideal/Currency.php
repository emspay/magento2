<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 10.11.17
 * Time: 11:27
 */

namespace EMS\Pay\Model\Source\Ideal;



class Currency
{
    protected $_currency;
    protected $_new_currency;
    protected $_options;

    public function __construct(

        \EMS\Pay\Model\Currency $currency,
        \Magento\Config\Model\Config\Source\Locale\Currency $new_currency
    )
    {
        $this->_currency = $currency;
        $this->_new_currency = $new_currency;
        $this->_options = $new_currency->toOptionArray();


    }

    /**
     * @inheritdoc
     */
    public function toOptionArray($isMultiselect = false)
    {
        foreach ($this->_options as $index => $optionData) {
            $value = $optionData['value'];
            if ($value !== '' && !$this->_currency->isCurrencySupportedByIdeal($value)) {
                unset($this->_options[$index]);
            } elseif ($value !== '') {
                $this->_options[$index]['label'] = $this->_currency->getCurrencyLabel($value);
            }
        }
        return $this->_options;
    }
}