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
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Registry $_coreRegistry
     * @param array $data
     * @internal param \EMS\Pay\Gateway\Config\Config|\EMS\Pay\Gateway\Config\ConfigFactory $emspayConfigFactory
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $_coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->registry = $_coreRegistry;
    }

    /**
     * Retrieves redirect form action url
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->registry->registry('emsRedirectUrl');
    }

    /**
     * @return array
     */
    public function getFormFields()
    {
        return $this->registry->registry('requestArguments');
    }


}
