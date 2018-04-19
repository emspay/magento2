<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 30.11.17
 * Time: 10:00
 */

namespace EMS\Pay\Controller;

use \EMS\Pay\Gateway\Config\Config;


abstract class EmsAbstract extends \Magento\Framework\App\Action\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    private $orderSender;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->orderSender = $orderSender;
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
     * Return customer quote
     *
     * @param bool $cancelOrder
     * @param string $errorMsg
     * @return void
     */
    protected function _returnCustomerQuoteSuccess($cancelOrder = false, $errorMsg = '')
    {
        $incrementId = $this->_getEmsPaySession()->getLastOrderIncrementId();
        if ($incrementId && $this->_getEmsPaySession()->isCheckoutOrderIncrementIdExist($incrementId)) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);
            if ($order->getId()) {
                try {
                    $this->orderSender->send($order);
                    /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
                    $quoteRepository = $this->_objectManager->create('Magento\Quote\Api\CartRepositoryInterface');
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $quoteRepository->get($order->getQuoteId());

                    $quote->setIsActive(0);
                    $quote->addMessage($errorMsg);
                    $quoteRepository->save($quote);
                    $this->_getCheckout()
                        ->setLastSuccessQuoteId($quote->getId())
                        ->setLastQuoteId($quote->getId())
                        ->setLastOrderId($order->getIncrementId())
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

                    $quote->setIsActive(1)->setReservedOrderId(null);
                    $quoteRepository->save($quote);
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

}