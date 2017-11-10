<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 10.11.17
 * Time: 11:27
 */

namespace Magento\EMS\Pay\Model\Adminhtml\Source;


use Magento\Config\Model\Config\Source\Locale\Currency\All;
use Magento\EMS\Pay\Gateway\Config\Config;

//use Magento\Framework\Option\ArrayInterface;

class Currency extends All
{
    protected $_config;
    protected $_currency;
    protected $_localeLists;
    protected $_options;

    public function __construct(

        Config $config,
        \Magento\EMS\Pay\Model\Adminhtml\Currency $currency
    )
    {
        $this->_options = parent::toOptionArray();
        $this->_config = $config;
        $this->_currency = $currency;

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