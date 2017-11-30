<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 30.11.17
 * Time: 12:15
 */

namespace EMS\Pay\Controller\Index;

use \EMS\Pay\Controller\EmsAbstract;
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
     * @var \Magento\Payment\Model\Method\Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \EMS\Pay\Model\ResponseFactory $responseFactory
     * @param \Magento\Payment\Model\Method\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \EMS\Pay\Model\ResponseFactory $responseFactory,
        \Magento\Payment\Model\Method\Logger $logger
    ) {
        parent::__construct($context, $coreRegistry);
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->responseFactory = $responseFactory;
    }


    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->logger->debug($this->getRequest()->getParams());

        try {
            /** @var \EMS\Pay\Model\Response $response */
            $response = $this->responseFactory->create(['response' => $this->getRequest()->getParams()]);
            $this->messageManager->addErrorMessage($response->getFailReason());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/cart', ['_secure' => true]);
        return $resultRedirect;

    }
}