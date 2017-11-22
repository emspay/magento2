<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 17.11.17
 * Time: 17:08
 */

namespace EMS\Pay\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use \EMS\Pay\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;

class ConfigProvider  implements ConfigProviderInterface
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;
    /**
     * @var \EMS\Pay\Model\Method\Mapper
     */
    private $mapper;

    /**
     * Constructor
     *
     * @param Config $config
     * @param ResolverInterface $localeResolver No longer used by internal code and not recommended.
     * @param \EMS\Pay\Model\Method\Mapper $mapper
     * @internal param PayPalConfig $payPalConfig No longer used by internal code and not recommended.
     * @internal param BraintreeAdapter $adapter
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Config $config,
        ResolverInterface $localeResolver,
        \EMS\Pay\Model\Method\Mapper $mapper
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->mapper = $mapper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                Config::METHOD_CC => [
                      'isActive' => $this->config->isActive(Config::METHOD_CC),
                       'availableCardTypes' => $this->config->getEnabledCreditCardTypes(),
                       'logoFileNames' => $this->config->getEnabledCreditCardTypesLogoImagesUrls(),
                        Config::XML_CONFIG_CC_3DSECURE => $this->config->isCreditCard3DSecureEnabled(),
                        'cardTypeFieldName' => 'ems_card_type'
                ],
            ]
        ];
    }

}