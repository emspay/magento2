<?php

namespace EMS\Pay\Controller\Index;

use EMS\Pay\Controller\EmsAbstract;
use EMS\Pay\Gateway\Config\Config;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class Redirect
 * @package EMS\Pay\Controller\Index
 */
class Redirect extends EmsAbstract
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param OrderSender $orderSender
     * @param Session $checkoutSession
     * @param JsonFactory $resultJsonFactory
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        OrderSender $orderSender,
        Session $checkoutSession,
        JsonFactory $resultJsonFactory,
        Config $config
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $orderSender, $config);
    }

    /**
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
