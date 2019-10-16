<?php

namespace EMS\Pay\Controller\Index;

if (interface_exists('Magento\Framework\App\CsrfAwareActionInterface')) {
    class Ipn extends IpnCsrfCompatible
    {
    }
} else {
    class Ipn extends IpnMain
    {
    }
}
