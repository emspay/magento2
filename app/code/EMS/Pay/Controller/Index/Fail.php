<?php

namespace EMS\Pay\Controller\Index;

use EMS\Pay\Controller\EmsAbstract;
use EMS\Pay\Model\Debugger;
use EMS\Pay\Gateway\Config\Config;
use EMS\Pay\Model\ResponseFactory;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class Fail
 * @package EMS\Pay\Controller\Index
 */
class Fail extends EmsAbstract
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * Fail constructor.
     * @param Context $context
     * @param OrderSender $orderSender
     * @param ResponseFactory $responseFactory
     * @param Config $config
     */
    public function __construct(
        Context $context,
        OrderSender $orderSender,
        ResponseFactory $responseFactory,
        Config $config
    ) {
        parent::__construct($context, $orderSender, $config);
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
