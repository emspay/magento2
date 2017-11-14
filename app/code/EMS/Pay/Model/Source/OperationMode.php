<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 10.11.17
 * Time: 13:41
 */

namespace EMS\Pay\Model\Source;

use \EMS\Pay\Gateway\Config\Config;

class OperationMode
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::MODE_TEST,
                'label' => __('Test mode')
            ],
            [
                'value' => Config::MODE_PRODUCTION,
                'label' => __('Live mode')
            ]
        ];
    }
}