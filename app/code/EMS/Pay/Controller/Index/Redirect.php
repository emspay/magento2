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
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $coreRegistry);
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
        $this->_coreRegistry->register('emsRedirectUrl', $emsRedirectUrl);
        $this->_coreRegistry->register('requestArguments', $requestArguments);
        try {
            $this->_view->addPageLayoutHandles();
            $this->_view->loadLayout(false)->renderLayout();
            $this->_getCheckout()->clearQuote();
            $this->_getCheckout()->clearHelperData();
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage($ex->getMessage());
            $this->messageManager->addErrorMessage(__('There was an error processing your order. Please contact us or try again later.'));
            $resultRedirect->setPath('*/*/error', ['_secure' => true]);
            return $resultRedirect;
        }

    }
}
