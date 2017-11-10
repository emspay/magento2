<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 10.11.17
 * Time: 13:36
 */

namespace Magento\EMS\Pay\Model\Source;

use \Magento\EMS\Pay\Gateway\Config\Config;

class DataTransferMode
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::DATA_TRANSFER_PAYONLY,
                'label' => __('Payment information')
            ],
            [
                'value' => Config::DATA_TRANSFER_PAYPLUS,
                'label' => __('Payment information + billing')
            ],
            [
                'value' => Config::DATA_TRANSFER_FULLPAY,
                'label' => __('Payment information + billing + shipping')
            ]
        ];
    }
}