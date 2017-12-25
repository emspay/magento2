<?php

namespace EMS\Pay\Controller\Index;

use EMS\Pay\Gateway\Config\Config;
use \EMS\Pay\Model\Response;
use \EMS\Pay\Controller\EmsAbstract;
use \EMS\Pay\Model\Debugger;
use Magento\Framework\Controller\ResultFactory;


class Success extends EmsAbstract
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
     * @var \Psr\Log\LoggerInterface
     */
    private $debugger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \EMS\Pay\Model\ResponseFactory $responseFactory
     * @param \EMS\Pay\Model\Debugger $debugger
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
            /** @var \EMS\Pay\Model\Response $response */
            $response = $this->responseFactory->create(['response' => $this->getRequest()->getParams()]);
            if ($response->getTransactionStatus() === Response::STATUS_WAITING) {
                $this->messageManager->addSuccessMessage(__('We are awaiting for payment confirmation.'));

            }
            $this->_returnCustomerQuoteSuccess();

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e);
        }


        $resultRedirect->setPath('checkout/onepage/success', ['_secure' => true]);
        return $resultRedirect;

    }
}
