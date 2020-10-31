<?php

namespace ClarityTech\Ezyslips\Api;

class Order extends Entity
{
    public static function isSingle() : bool
    {
        return true;
    }
   
    /**
     * @param array $params
     *
     * @return ClarityTech\Ezyslips\Api\Order
     */
    public function fetch(array $params = [])
    {
        $this->setEntityUrl('getorders');
        //orderid
        $entity = parent::fetch($params);

        if ($entity instanceof EzyslipsResponse) {
            return $entity->raiseError();
        }
        
        return $entity;
    }

    /**
     * @param array $params
     *
     * @return ClarityTech\Ezyslips\Api\Order
     */
    public function all(array $params = [])
    {
        $this->setEntityUrl('getorders');
        return parent::all($params);
    }

    /**
     * This API Restful that enables merchants
     * to count orders from Ezyslips system
     * @param array $params
     *
     * @return ClarityTech\Ezyslips\Api\Order
     */
    public function count()
    {
        $this->setEntityUrl('countorders');
        return parent::fetch();
    }

    /**
     * @param $id Order id description
     */
    public function create(array $attributes = [])
    {
        $this->setEntityUrl('v2orders');

        return parent::create($attributes);
    }
}
