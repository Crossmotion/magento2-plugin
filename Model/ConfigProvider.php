<?php

namespace Paynl\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        'paynl_payment_afterpay',
        'paynl_payment_afterpay_international',
        'paynl_payment_alipay',
        'paynl_payment_amazonpay',
        'paynl_payment_amex',
        'paynl_payment_applepay',
        'paynl_payment_bataviacadeaukaart',
        'paynl_payment_biercheque',
        'paynl_payment_biller',
        'paynl_payment_billink',
        'paynl_payment_blik',
        'paynl_payment_decadeaukaart',
        'paynl_payment_capayable',
        'paynl_payment_capayable_gespreid',
        'paynl_payment_cartasi',
        'paynl_payment_cartebleue',
        'paynl_payment_cashly',
        'paynl_payment_clickandbuy',
        'paynl_payment_creditclick',
        'paynl_payment_dankort',
        'paynl_payment_eps',
        'paynl_payment_fashioncheque',
        'paynl_payment_fashiongiftcard',
        'paynl_payment_focum',
        'paynl_payment_gezondheidsbon',
        'paynl_payment_giropay',
        'paynl_payment_givacard',
        'paynl_payment_good4fun',
        'paynl_payment_googlepay',
        'paynl_payment_huisentuincadeau',
        'paynl_payment_ideal',
        'paynl_payment_instore',
        'paynl_payment_klarna',
        'paynl_payment_klarnakp',
        'paynl_payment_maestro',
        'paynl_payment_mistercash',
        'paynl_payment_multibanco',
        'paynl_payment_mybank',
        'paynl_payment_nexi',
        'paynl_payment_overboeking',
        'paynl_payment_onlinebankbetaling',
        'paynl_payment_payconiq',
        'paynl_payment_paypal',
        'paynl_payment_paysafecard',
        'paynl_payment_podiumcadeaukaart',
        'paynl_payment_postepay',
        'paynl_payment_przelewy24',
        'paynl_payment_shoesandsneakers',
        'paynl_payment_sofortbanking',
        'paynl_payment_sofortbanking_hr',
        'paynl_payment_sofortbanking_ds',
        'paynl_payment_spraypay',
        'paynl_payment_telefonischbetalen',
        'paynl_payment_tikkie',
        'paynl_payment_trustly',
        'paynl_payment_visamastercard',
        'paynl_payment_vvvgiftcard',
        'paynl_payment_webshopgiftcard',
        'paynl_payment_wechatpay',
        'paynl_payment_wijncadeau',
        'paynl_payment_yehhpay',
        'paynl_payment_yourgift',
        'paynl_payment_yourgreengift'
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Config
     */
    protected $paynlConfig;

    /**
     * @var \Magento\Payment\Model\Config|Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    protected $resolver;

    private $_store = null;

    /**
     * ConfigProvider constructor.
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param Config $paynlConfig
     * @param Magento\Payment\Model\Config $paymentConfig
     * @param Magento\Framework\Locale\Resolver $resolver
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Config $paynlConfig,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\Locale\Resolver $resolver
    ) {
        $this->paynlConfig = $paynlConfig;
        $this->escaper = $escaper;
        $this->paymentConfig = $paymentConfig;
        $this->resolver = $resolver;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];

        $locale = $this->resolver->getLocale();
        $localeParts = explode('_', $locale);
        $language = isset($localeParts[0]) ? mb_strtoupper($localeParts[0]) : 'NL';

        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['instructions'][$code] = $this->getInstructions($code);
                $config['payment']['paymentoptions'][$code]        = $this->getPaymentOptions($code);
                $config['payment']['showpaymentoptions'][$code]    = $this->showPaymentOptions($code);
                $config['payment']['defaultpaymentoption'][$code]  = $this->getDefaultPaymentOption($code);
                $config['payment']['hidepaymentoptions'][$code]    = $this->hidePaymentOptions($code);
                $config['payment']['icon'][$code]         = $this->getIcon($code);
                $config['payment']['showkvk'][$code]      = $this->getKVK($code);
                $config['payment']['showvat'][$code]      = $this->getVAT($code);
                $config['payment']['showdob'][$code]      = $this->getDOB($code);
                $config['payment']['showforcompany'][$code] = $this->getCompany($code);
                $config['payment']['showforgroup'][$code] = $this->getCustomerGroup($code);

                $config['payment']['disallowedshipping'][$code] = $this->getDisallowedShippingMethods($code);
                $config['payment']['currentipisvalid'][$code]    = $this->methods[$code]->isCurrentIpValid();
                $config['payment']['currentagentisvalid'][$code] = $this->methods[$code]->isCurrentAgentValid();
                $config['payment']['defaultpaymentmethod'][$code] = $this->methods[$code]->isDefaultPaymentOption();

                $config['payment']['public_encryption_keys'][$code] = $this->getPublicEncryptionKeys($code);
                $config['payment']['cc_months'][$code] = $this->paymentConfig->getMonths();
                $config['payment']['cc_years'][$code] = $this->paymentConfig->getYears();
                $config['payment']['language'][$code] = $language;

                if ($code == 'paynl_payment_visamastercard') {
                    $config['payment']['cse_enabled'] = $this->getConfigItem('cse_enabled', 'visamastercard');
                    $config['payment']['cse_success_popup'] = $this->getConfigItem('cse_success_popup', 'visamastercard');
                    $config['payment']['cse_error_popup'] = $this->getConfigItem('cse_error_popup', 'visamastercard');
                    $config['payment']['cse_payment_popup'] = $this->getConfigItem('cse_payment_popup', 'visamastercard');
                    $config['payment']['cse_color'] = $this->getConfigItem('cse_colored_fields', 'visamastercard');
                    $config['payment']['cse_debug'] = $this->getConfigItem('cse_pay_debug', 'visamastercard');
                    $config['payment']['cse_finish_delay'] = $this->getConfigItem('cse_modal_payment_complete_redirection_timeout', 'visamastercard');
                }
            }
        }

        $config['payment']['testMode']                = $this->paynlConfig->isTestMode();
        $config['payment']['useAdditionalValidation'] = $this->paynlConfig->getUseAdditionalValidation();

        return $config;
    }

    /**
     * @param $item
     * @param $method
     * @return mixed
     */
    protected function getConfigItem($item, $method)
    {
        if (empty($this->_store)) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_store = $om->get(\Magento\Store\Model\Store::class);
        }

        return $this->_store->getConfig('payment/paynl_payment_' . $method . '/' . $item);
    }

    /**
     * @param $code
     * @return mixed
     */
    protected function getPublicEncryptionKeys($code)
    {
        return $this->methods[$code]->getPublicEncryptionKeys();
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     *
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
    }

    protected function getPaymentOptions($code)
    {
        return $this->methods[$code]->getPaymentOptions();
    }

    protected function showPaymentOptions($code)
    {
        return $this->methods[$code]->showPaymentOptions();
    }

    protected function getDefaultPaymentOption($code)
    {
        return $this->methods[$code]->getDefaultPaymentOption();
    }

    protected function hidePaymentOptions($code)
    {
        return $this->methods[$code]->hidePaymentOptions();
    }

    protected function getKVK($code)
    {
        return $this->methods[$code]->getKVK();
    }

    protected function getVAT($code)
    {
        return $this->methods[$code]->getVAT();
    }

    protected function getDOB($code)
    {
        return $this->methods[$code]->getDOB();
    }

    protected function getDisallowedShippingMethods($code)
    {
        return $this->methods[$code]->getDisallowedShippingMethods();
    }

    protected function getCompany($code)
    {
        return $this->methods[$code]->getCompany();
    }

    protected function getCustomerGroup($code)
    {
        return $this->methods[$code]->getCustomerGroup();
    }

    /**
     * Get payment method icon
     *
     * @param string $code
     *
     * @return string
     */
    protected function getIcon($code)
    {
        $url = $this->paynlConfig->getIconUrl($code, $this->methods[$code]->getPaymentOptionId());
        return $url;
    }
}
