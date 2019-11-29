<?php

namespace EMS\Pay\Controller;

use EMS\Pay\Gateway\Config\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class EmsAbstract
 * @package EMS\Pay\Controller
 */
abstract class EmsAbstract extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param OrderSender $orderSender
     * @param Config $config
     */
    public function __construct(
        Context $context,
        OrderSender $orderSender,
        Config $config
    ) {
        parent::__construct($context);
        $this->orderSender = $orderSender;
        $this->config = $config;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * Get session model
     *
     * @return \EMS\Pay\Model\Session
     */
    protected function _getEmsPaySession()
    {
        return $this->_objectManager->get('\EMS\Pay\Model\Session');
    }

    /**
     * @param bool $cancelOrder
     * @param string $errorMsg
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _returnCustomerQuoteSuccess($cancelOrder = false, $errorMsg = '')
    {
        $incrementId = $this->_getEmsPaySession()->getLastOrderIncrementId();
        if ($incrementId && $this->_getEmsPaySession()->isCheckoutOrderIncrementIdExist($incrementId)) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            if ($order->getId()) {
                try {
                    if ($this->config->isOrderConfirmationEmailSending()) {
                        $this->orderSender->send($order, $this->config->isForceSyncModeEmailSending());
                    }
                    /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
                    $quoteRepository = $this->_objectManager->create('Magento\Quote\Api\CartRepositoryInterface');
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $quoteRepository->get($order->getQuoteId());

                    $quote->setIsActive(0);
                    $quoteRepository->save($quote);
                    $this->_getCheckout()
                        ->setLastSuccessQuoteId($quote->getId())
                        ->setLastQuoteId($quote->getId())
                        ->setLastOrderId($order->getId())
                        ->setLastRealOrderId($order->getIncrementId());
                    $this->_getCheckout()->replaceQuote($quote);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    throw new \Magento\Framework\Exception\NoSuchEntityException($e);
                }
                $this->_getEmsPaySession()->removeCheckoutOrderIncrementId($incrementId);
                $this->_getEmsPaySession()->unsetData('quote_id');
                if ($cancelOrder) {
                    $order->cancel($errorMsg)->save();
                }
            }
        }
    }

    /**
     * @param bool $cancelOrder
     * @param string $errorMsg
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _returnCustomerQuoteError($cancelOrder = false, $errorMsg = '')
    {
        $incrementId = $this->_getEmsPaySession()->getLastOrderIncrementId();
        if ($incrementId && $this->_getEmsPaySession()->isCheckoutOrderIncrementIdExist($incrementId)) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            if ($order->getId()) {
                try {
                    /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
                    $quoteRepository = $this->_objectManager->create('Magento\Quote\Api\CartRepositoryInterface');
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $quoteRepository->get($order->getQuoteId());
                    $quote->addErrorInfo('error', null, null, $errorMsg);
                    $quote->setIsActive(1)->setReservedOrderId(null);
                    $quoteRepository->save($quote);
                    $this->_getCheckout()->replaceQuote($quote);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    throw new \Magento\Framework\Exception\NoSuchEntityException($e);
                }
                $this->_getEmsPaySession()->removeCheckoutOrderIncrementId($incrementId);
                $this->_getEmsPaySession()->unsetData('quote_id');
                if ($cancelOrder) {
                    $order->cancel($errorMsg);
                    $order->save();
                }
            }
        }
    }
}
