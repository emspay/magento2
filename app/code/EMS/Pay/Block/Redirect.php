<?php
namespace EMS\Pay\Block;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;

class Redirect extends \Magento\Payment\Block\Form
{

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_methodCode;



    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var null
     */
    protected $_config;

    /**
     * @var bool
     */
    protected $_isScopePrivate;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    protected $paymentMethod;
    /**
     * @var \EMS\Pay\Gateway\Config\Config
     */
    private $emspayConfigFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @param Context $context
     * @param \EMS\Pay\Gateway\Config\Config|\EMS\Pay\Gateway\Config\ConfigFactory $emspayConfigFactory
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \EMS\Pay\Gateway\Config\ConfigFactory $emspayConfigFactory,
        ResolverInterface $localeResolver,
//        Data $paypalData,
        CurrentCustomer $currentCustomer,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
//        $this->_paypalData = $paypalData;
//        $this->_emspayConfigFactory= $emspayConfigFactory;
        $this->_localeResolver = $localeResolver;
        $this->_config = null;
        $this->_isScopePrivate = true;
        $this->currentCustomer = $currentCustomer;
        $this->emspayConfigFactory = $emspayConfigFactory;
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->paymentMethod = $this->_getPaymentMethod();
    }

    /**
     * Retrieves redirect form action url
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->paymentMethod->getGatewayUrl();
    }

    /**
     * @return array
     */
    public function getFormFields()
    {
        return $this->paymentMethod->getRedirectFormFields();
    }

    /**
     * @return \EMS\Pay\Model\Method\EmsAbstractMethod
     * @throws \Exception
     */
    protected function _getPaymentMethod()
    {
        $paymentMethod = null;
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            $paymentMethod = $order->getPayment()->getMethodInstance();
        }
        if (is_null($paymentMethod) || !($paymentMethod instanceof \EMS\Pay\Model\Method\EmsAbstractMethod)) {
            throw new \Exception(__('Payment method %s is not supported', get_class($paymentMethod)));
        }
        return $paymentMethod;
    }

}
