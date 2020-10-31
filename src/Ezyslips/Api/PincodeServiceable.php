<?php

namespace ClarityTech\Ezyslips\Api;

class PincodeServiceable extends Entity
{
    protected string $resourcePath = 'pincodeserviceable';

    public static function isSingle() : bool
    {
        return true;
    }
   
    /**
     * @param int $pincode
     * @param string $paymentType P - Prepaid, C - COD
     *
     * @return ClarityTech\Ezyslips\Api\PincodeServiceable
     */
    public function check(int $pincode, ?string $paymentType = null)
    {
        $params = ['pincode' => $pincode];

        if (!is_null($paymentType)) {
            $params['payment_type'] = $paymentType;
        }
        return parent::fetch($params);
    }
}
