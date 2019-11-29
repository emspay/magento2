<?php

namespace EMS\Pay\Controller\Index;

use EMS\Pay\Controller\EmsAbstract;
use EMS\Pay\Gateway\Config\Config;
use EMS\Pay\Model\Debugger;
use EMS\Pay\Model\IpnFactory;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

/**
 * Class IpnMain
 * @package EMS\Pay\Controller\Index
 */
class IpnMain extends EmsAbstract
{
    /**
     * @var \EMS\Pay\Model\Ipn
     */
    protected $ipn;

    /**
     * @var IpnFactory
     */
    private $ipnFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * IpnMain constructor.
     * @param Context $context
     * @param OrderSender $orderSender
     * @param IpnFactory $ipnFactory
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        Context $context,
        OrderSender $orderSender,
        IpnFactory $ipnFactory,
        LoggerInterface $logger,
        Config $config
    ) {
        parent::__construct($context, $orderSender, $config);
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
            $data = $this->getRequest()->getPostValue();
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
