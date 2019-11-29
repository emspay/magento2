<?php

namespace EMS\Pay\Controller\Index;

use EMS\Pay\Model\Debugger;
use EMS\Pay\Gateway\Config\Config;
use EMS\Pay\Controller\EmsAbstract;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class Error
 * @package EMS\Pay\Controller\Index
 */
class Error extends EmsAbstract
{
    /**
     * Error constructor.
     *
     * @param Context $context
     * @param OrderSender $orderSender
     * @param Config $config
     */
    public function __construct(
        Context $context,
        OrderSender $orderSender,
        Config $config
    ) {
        parent::__construct($context, $orderSender, $config);
    }

    /**
     *  Action used to restore quote if exception occurred while redirecting user to payment gateway if payment failed
     * @inheritdoc
     */
    public function execute()
    {
        Debugger::debug($this->getRequest()->getParams(), Config::DEFAULT_LOG_FILE);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart', ['_secure' => true]);

        if (!$this->getRequest()->isPost()) {
            return $resultRedirect;
        }
        try {
            $message = __('Order cancelled because of error');
            $this->_returnCustomerQuoteError(true, $message);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e);
        }

        $resultRedirect->setPath('checkout', ['_secure' => true]);
        return $resultRedirect;
    }
}
