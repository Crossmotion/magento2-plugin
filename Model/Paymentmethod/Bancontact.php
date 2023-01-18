<?php

namespace Paynl\Payment\Model\Paymentmethod;

class Bancontact extends PaymentMethod
{
    protected $_code = 'paynl_payment_bancontact';

    /**
     * @return int
     */
    protected function getDefaultPaymentOptionId()
    {
        return 436;
    }
}
