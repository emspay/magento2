<?php

namespace EMS\Pay\Model\Source;

use EMS\Pay\Gateway\Config\Config;


class CcType extends \Magento\Payment\Model\Source\Cctype
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }


    public function toOptionArray()
    {

        $options = [];
        foreach ($this->config->getAvailableCreditCardTypes() as $code => $name) {
            $options[] = [
                'value' => $code,
                'label' => $name
            ];
        }

        return $options;
    }

}

