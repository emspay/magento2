<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EMS\Pay\Controller\Index;


/**
 * DirectPost Payment Controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Redirect extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Authorizenet\Helper\DataFactory
     */
    protected $dataFactory;
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;
    /**
     * @var \EMS\Pay\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \EMS\Pay\Model\SessionFactory $sessionFactory
     * @internal param \Magento\Authorizenet\Helper\DataFactory $dataFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \EMS\Pay\Model\SessionFactory $sessionFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
        $this->context = $context;
        $this->sessionFactory = $sessionFactory;
    }


    /**
     *
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->checkoutSession->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();
        $emsPaySession = $this->sessionFactory->get();
        $emsPaySession->setQuoteId($this->checkoutSession->getQuoteId());
        $emsPaySession->addCheckoutOrderIncrementId($order->getIncrementId());
        $emsPaySession->setLastOrderIncrementId($order->getIncrementId());
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
            $this->checkoutSession->clearQuote();
            $this->checkoutSession->clearHelperData();
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage($ex->getMessage());
            $this->checkoutSession->setCancelOrder(true);
            $this->messageManager->addErrorMessage(__('There was an error processing your order. Please contact us or try again later.'));
            $this->_redirect('*/*/error');
        }

    }
}
