<?php

namespace EMS\Pay\Controller\Index;

use \EMS\Pay\Model\Response;
use \EMS\Pay\Model\Debugger;
use EMS\Pay\Gateway\Config\Config;
use \EMS\Pay\Controller\EmsAbstract;
use Magento\Framework\Controller\ResultFactory;

class Error extends EmsAbstract
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $responseFactory;
    /**
     * @var \EMS\Pay\Model\Debugger
     */
    private $debugger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \EMS\Pay\Model\ResponseFactory $responseFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \EMS\Pay\Model\ResponseFactory $responseFactory
    )
    {
        parent::__construct($context, $coreRegistry, $orderSender);
        $this->checkoutSession = $checkoutSession;
        $this->_coreRegistry = $coreRegistry;
        $this->responseFactory = $responseFactory;
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
                $message = __('Order canceled because of error');
                $this->_returnCustomerQuoteError(true, $message);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e);
        }


        $resultRedirect->setPath('checkout', ['_secure' => true]);
        return $resultRedirect;

    }
}
