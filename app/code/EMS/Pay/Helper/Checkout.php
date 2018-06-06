<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 23.11.17
 * Time: 12:09
 */

namespace EMS\Pay\Helper;


use Magento\Quote\Model\Quote;
use Magento\TestFramework\Event\Magento;

class Checkout extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order = null;

    /**
     * Restore last active quote based on checkout session
     *
     * @return bool True if quote restored successfully, false otherwise
     */
    public function restoreQuote()
    {
        $order = $this->getLastRealOrder();
        if ($order->getId()) {
            $quote = $this->_getQuote($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(1)
                    ->setReservedOrderId(null)
                    ->save();
                $this->_getCheckoutSession()
                    ->replaceQuote($quote)
                    ->unsLastRealOrderId();
                return true;
            }
        }
        return false;
    }

    /**
     * Get order instance based on last order ID
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function getLastRealOrder()
    {
        $orderId = $this->_getCheckoutSession()->getLastRealOrderId();
        if ($this->_order !== null && $orderId == $this->_order->getIncrementId()) {
            return $this->_order;
        }

        $this->_order = Mage::getModel('sales/order');
        if ($orderId) {
            $this->_order->loadByIncrementId($orderId);
        }

        if (!$this->_order->getId()) {
            throw new \Exception(__('Order for id %s not found', $orderId));
        }

        return $this->_order;
    }

    /**
     * Return checkout session instance
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return sales quote instance for specified ID
     *
     * @param int $quoteId Quote identifier
     * @return Quote
     */
    protected function _getQuote($quoteId)
    {
        return Mage::getModel('sales/quote')->load($quoteId);
    }
}