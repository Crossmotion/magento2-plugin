<?php

namespace Paynl\Payment\Model\Paymentmethod;

class Decadeaukaart extends PaymentMethod
{
    protected $_code = 'paynl_payment_decadeaukaart';

    /**
     * @return integer
     */
    protected function getDefaultPaymentOptionId()
    {
        return 2601;
    }
}
