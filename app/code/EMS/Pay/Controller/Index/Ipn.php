<?php

namespace EMS\Pay\Controller\Index;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

if (interface_exists('Magento\Framework\App\CsrfAwareActionInterface')) {
    class Ipn extends IpnMain implements CsrfAwareActionInterface
    {
        /**
         * @inheritDoc
         */
        public function createCsrfValidationException(
            RequestInterface $request
        ): ?InvalidRequestException {
            return null;
        }

        /**
         * @inheritDoc
         */
        public function validateForCsrf(RequestInterface $request): ?bool
        {
            return true;
        }
    }
} else {
    class Ipn extends IpnMain
    {
    }
}
