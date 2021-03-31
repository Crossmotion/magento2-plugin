<?php
/**
 * Copyright © 2020 PAY. All rights reserved.
 */

namespace Paynl\Payment\Model\Paymentmethod;

/**
 * Class Afterpay
 * @package Paynl\Payment\Model\Paymentmethod
 */
class Afterpay extends PaymentMethod
{
    protected $_code = 'paynl_payment_afterpay';

    protected function getDefaultPaymentOptionId()
    {
        return 739;
    }

    public function getDOB()
    {
        return $this->_scopeConfig->getValue('payment/paynl_payment_afterpay/showdob', 'store');
    }
}