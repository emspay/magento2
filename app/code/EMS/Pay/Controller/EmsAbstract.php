<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 30.11.17
 * Time: 10:00
 */

namespace EMS\Pay\Controller;


abstract class EmsAbstract extends \Magento\Framework\App\Action\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
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
     * @return \Magento\Authorizenet\Model\Directpost\Session
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
                    /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
                    $quoteRepository = $this->_objectManager->create('Magento\Quote\Api\CartRepositoryInterface');
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $quoteRepository->get($order->getQuoteId());

                    $quote->setIsActive(0);
                    $quoteRepository->save($quote);
                    $this->_getCheckout()
                        ->setLastSuccessQuoteId($quote->getId())
                        ->setLastQuoteId($quote->getId())
                        ->setLastOrderId($order->getIncrementId())
                        ->setLastRealOrderId($order->getIncrementId());
                    $this->_getCheckout()->replaceQuote($quote);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                }
                $this->_getEmsPaySession()->removeCheckoutOrderIncrementId($incrementId);
                $this->_getEmsPaySession()->unsetData('quote_id');
                if ($cancelOrder) {
                    $order->registerCancellation($errorMsg)->save();
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
                }
                $this->_getEmsPaySession()->removeCheckoutOrderIncrementId($incrementId);
                $this->_getEmsPaySession()->unsetData('quote_id');
                if ($cancelOrder) {
                    $order->registerCancellation($errorMsg)->save();
                }
            }
        }
    }

}