<?php

namespace Paynl\Payment\Model\Paymentmethod;

class Yehhpay extends PaymentMethod
{
    protected $_code = 'paynl_payment_yehhpay';

    protected function getDefaultPaymentOptionId()
    {
        return 1877;
    }
}
