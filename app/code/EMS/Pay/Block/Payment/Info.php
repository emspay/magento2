<?php

namespace EMS\Pay\Block\Payment;


class Info extends \Magento\Payment\Block\Info\Cc
{

    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;
    /**
     * @var \EMS\Pay\Model\Info
     */
    private $info;
    /**
     * @var \EMS\Pay\Gateway\Config\Config
     */
    private $config;
    /**
     * @var \EMS\Pay\Model\InfoFactory
     */
    private $_infoFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \EMS\Pay\Model\Info $info
     * @param \EMS\Pay\Gateway\Config\Config $config
     * @internal param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \EMS\Pay\Model\InfoFactory $infoFactory,
        \EMS\Pay\Gateway\Config\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->_paymentConfig = $paymentConfig;
        $this->config = $config;
        $this->_infoFactory = $infoFactory;
    }


    /**
     * Don't show CC type for non-CC methods
     *
     * @return string|null
     */
    public function getCcTypeName()
    {
        if ($this->config->isCreditCardMethod($this->getInfo()->getMethod())) {
            return parent::getCcTypeName();
        }
    }

    /**
     * Prepare information specific to current payment method
     *
     * @param null|\Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $this->info = $this->_infoFactory->create();
        $payment = $this->getInfo();

        if ($this->getInfo()->getCcLast4()) {
            $transport->addData([__('Credit Card Number')->getText() => $this->getInfo()->getCcLast4()]);
        }

        if ($this->getIsSecureMode()) {
            $info = $this->info->getPublicPaymentInfo($payment);
        } else {
            $info = $this->info->getPaymentInfo($payment);
        }

        return $transport->addData($info);
    }

}