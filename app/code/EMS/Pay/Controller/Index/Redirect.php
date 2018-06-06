<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EMS\Pay\Controller\Index;

use \EMS\Pay\Controller\EmsAbstract;

class Redirect extends EmsAbstract
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
    private $context;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Redirect constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $coreRegistry, $orderSender);
        $this->context = $context;
    }


    /**
     *
     *
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->checkoutSession->getLastSuccessQuoteId()) {
            $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
            return $resultRedirect;
        }
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();
        $this->_getEmsPaySession()->setQuoteId($order->getQuoteId());
        $this->_getEmsPaySession()->addCheckoutOrderIncrementId($order->getIncrementId());
        $this->_getEmsPaySession()->setLastOrderIncrementId($order->getIncrementId());
        if (!$payment || !($payment->getMethodInstance() instanceof \EMS\Pay\Model\Method\EmsAbstractMethod)) {
            $this->messageManager->addErrorMessage('Payment method %s is not supported', get_class($payment->getMethodInstance()));
        }
        $method = $payment->getMethodInstance();
        $emsRedirectUrl = $method->getGatewayUrl();
        $requestArguments = $method->generateRequestFromOrder($order);
        //$this->_coreRegistry->register('emsRedirectUrl', $emsRedirectUrl);
        //$this->_coreRegistry->register('requestArguments', $requestArguments);
        $joineJsonArray['action'] = $emsRedirectUrl;
        $joineJsonArray['fields'] = $requestArguments; 
        try {
            //$this->_view->addPageLayoutHandles();
            //$this->_view->loadLayout(false)->renderLayout();
            $this->_getCheckout()->clearQuote();
            $this->_getCheckout()->clearHelperData();
            return $this->resultJsonFactory->create()->setData($joineJsonArray);
            
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage($ex->getMessage());
            $this->messageManager->addErrorMessage(__('There was an error processing your order. Please contact us or try again later.'));
            $resultRedirect->setPath('*/*/error', ['_secure' => true]);
            return $resultRedirect;
        }

    }
}
