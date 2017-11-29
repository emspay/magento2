<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace EMS\Pay\Controller\Index;

use \EMS\Pay\Model\Response;

/**
 * DirectPost Payment Controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Success extends \Magento\Framework\App\Action\Action
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
     * @var \EMS\Pay\Model\ResponseFactory
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
     * @internal param \Magento\Authorizenet\Helper\DataFactory $dataFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \EMS\Pay\Model\ResponseFactory $responseFactory,
        \Magento\Payment\Model\Method\Logger $logger
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->responseFactory = $responseFactory;
    }


    /**
     *
     *
     * @return void
     */
    public function execute()
    {
//        $this->logger->debug($this->getRequest()->getParams());

        if (!$this->getRequest()->isPost()) {
            return;
        }
        try {
            /** @var \EMS\Pay\Model\Response $response */
            $response = $this->responseFactory->create(['response' => $this->getRequest()->getParams()]);
            $emsQuoteId = $this->checkoutSession->getEmsQuoteId(true);
            if ($response->getTransactionStatus() === Response::STATUS_WAITING) {
                $this->messageManager->addSuccessMessage(__('We are awaiting for payment confirmation.'));
            }
            $this->checkoutSession->setQuoteId($emsQuoteId);
            $quote = $this->checkoutSession->getQuote();
            $quote->setIsActive(false);
            $quote->save();
            $orderId = $this->checkoutSession->getLastRealOrder()->getId();
            $this->_redirect('checkout/onepage/success', array('_secure'=>true));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e);
        }



    }
}
