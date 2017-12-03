<?php

namespace EMS\Pay\Controller\Index;

use EMS\Pay\Gateway\Config\Config;
use \EMS\Pay\Model\Response;
use \EMS\Pay\Controller\EmsAbstract;
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
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \EMS\Pay\Model\ResponseFactory $responseFactory
     * @param \EMS\Pay\Model\Debugger $debugger
     * @param \EMS\Pay\Model\IpnFactory $ipnFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \EMS\Pay\Model\ResponseFactory $responseFactory,
        \EMS\Pay\Model\Debugger $debugger,
        \EMS\Pay\Model\IpnFactory $ipnFactory
    )
    {
        parent::__construct($context, $coreRegistry);
        $this->checkoutSession = $checkoutSession;
        $this->$debugger = $debugger;
        $this->_coreRegistry = $coreRegistry;
        $this->responseFactory = $responseFactory;
        $this->ipnFactory = $ipnFactory;
    }


    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->debugger::debug(var_export($this->getRequest()->getParams()), Config::DEFAULT_LOG_FILE);
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
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
        return;
    }
}
