<?php

namespace EMS\Pay\Controller\Index;

use EMS\Pay\Gateway\Config\Config;
use \EMS\Pay\Model\Response;
use \EMS\Pay\Controller\EmsAbstract;
use \EMS\Pay\Model\Debugger;
use Magento\Framework\Controller\ResultFactory;

class Ipn extends EmsAbstract
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \EMS\Pay\Model\Ipn
     */
    protected $ipn;

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
     * @var \EMS\Pay\Model\IpnFactory
     */
    private $ipnFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \EMS\Pay\Model\ResponseFactory $responseFactory
     * @param \EMS\Pay\Model\IpnFactory $ipnFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \EMS\Pay\Model\ResponseFactory $responseFactory,
        \EMS\Pay\Model\IpnFactory $ipnFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        parent::__construct($context, $coreRegistry, $orderSender);
        $this->checkoutSession = $checkoutSession;
        $this->_coreRegistry = $coreRegistry;
        $this->responseFactory = $responseFactory;
        $this->ipnFactory = $ipnFactory;
        $this->logger = $logger;
    }


    /**
     * @inheritdoc
     */
    public function execute()
    {
        Debugger::debug($this->getRequest()->getPostValue(), Config::IPN_LOG_FILE);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart', ['_secure' => true]);

        if (!$this->getRequest()->isPost()) {
            return $resultRedirect;
        }
        try {
            /** @var \EMS\Pay\Model\Ipn $ipn */
            $data = $this->getRequest()->getParams();
            $this->ipn = $this->ipnFactory->create();
            $this->ipn->processIpnRequest($data);
        } catch (RemoteServiceUnavailableException $e) {
            $this->logger->critical($e);
            $this->getResponse()->setStatusHeader(503, '1.1', 'Service Unavailable')->sendResponse();
            /** @todo eliminate usage of exit statement */
            exit;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
}
