<?php

namespace Magento\EMS\Pay\Model\Adminhtml\Source;

use Magento\EMS\Pay\Gateway\Config\Config;


class CheckoutOption
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::CHECKOUT_OPTION_CLASSIC,
                'label' => __('Classic')
            ],
            [
                'value' => Config::CHECKOUT_OPTION_COMBINEDPAGE,
                'label' => __('Combined page')
            ]
        ];
    }

}