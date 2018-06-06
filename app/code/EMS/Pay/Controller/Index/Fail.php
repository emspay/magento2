<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 30.11.17
 * Time: 12:15
 */

namespace EMS\Pay\Controller\Index;

use \EMS\Pay\Controller\EmsAbstract;
use \EMS\Pay\Model\Debugger;
use \EMS\Pay\Gateway\Config\Config;
use \Magento\Framework\Controller\ResultFactory;

class Fail extends EmsAbstract
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
     * @internal param \Magento\Payment\Model\Method\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \EMS\Pay\Model\ResponseFactory $responseFactory
    ) {
        parent::__construct($context, $coreRegistry, $orderSender);
        $this->checkoutSession = $checkoutSession;
        $this->_coreRegistry = $coreRegistry;
        $this->responseFactory = $responseFactory;
    }


    /**
     * @inheritdoc
     */
    public function execute()
    {
        Debugger::debug($this->getRequest()->getParams(), Config::DEFAULT_LOG_FILE);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart', ['_secure' => true]);

        try {
            /** @var \EMS\Pay\Model\Response $response */
            $response = $this->responseFactory->create(['response' => $this->getRequest()->getParams()]);
            $this->messageManager->addErrorMessage($response->getFailReason());
            $this->_returnCustomerQuoteError(true, $response->getFailReason());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e);
        }
        return $resultRedirect;

    }
}