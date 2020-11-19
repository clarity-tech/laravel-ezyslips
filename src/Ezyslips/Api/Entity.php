<?php

namespace ClarityTech\Ezyslips\Api;

use ClarityTech\Ezyslips\Facades\Ezyslips;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

class Entity extends Resource implements Arrayable
{
    protected string $resourcePath = '';
    
    public function getEntityUrl() : string
    {
        return $this->resourcePath;
    }

    public function setEntityUrl(string $path)
    {
        $this->resourcePath = $path;
        return $this;
    }

    protected function getEntityClassName()
    {
        $fullClassName = get_class($this);
        $pos = strrpos($fullClassName, '\\');
        $className = substr($fullClassName, $pos + 1);
        $className = Str::of($className)->snake();
        return $className;
    }

    protected static function getEntityClass($name)
    {
        return __NAMESPACE__.'\\'.ucfirst($name);
    }

    protected static function resolveEntityName(string $name)
    {
        $entityName = Str::of($name)->camel();
        $entityName = ucfirst($entityName);

        return $entityName;
    }

    public static function getDefinedEntitiesArray()
    {
        return ['order', 'product', 'pickup_address', 'return_address'];
    }

    public function toArray()
    {
        return $this->convertToArray($this->attributes);
    }

    protected function convertToArray($attributes) : array
    {
        $array = $attributes;

        foreach ($attributes as $key => $value) {
            if (is_object($value)) {
                $array[$key] = $value->toArray();
            } elseif (is_array($value) and self::isAssocArray($value) == false) {
                $array[$key] = $this->convertToArray($value);
            }
        }

        return $array;
    }

    public static function isAssocArray(array $arr) : bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    // public function getFullUrl($resource)
    // {
    //     return Ezyslips::getBaseUrl() . $resource;
    // }

    /**
     * Makes a HTTP request using Request class and assuming the API returns
     * formatted entity or collection result, wraps the returned JSON as entity
     * and returns.
     *
     * @param string $method
     * @param string $relativeUrl
     * @param array  $data
     * @param bool  $first if set to true it will return the first record
     *
     * @return Entity
     */
    protected function request(string $method, $entityUrl, array $params = [], bool $first = false)
    {
        $response = Ezyslips::$method($entityUrl, $params);

        if (is_object($response)) {
            $response = $response->json();
        }

        $ezr = EzyslipsResponse::validateAndParseIfSimple($response);

        if ($ezr->isSimple()) {
            return $ezr;
        }


        if (is_array($response['message']) && $first) {
            //count($response['message']) == 1
            return static::buildEntity(head($response['message']));
        }

        return static::buildEntity($response['message']);
    }

    

    /**
     * Given the JSON response of an API call, wraps it to corresponding entity
     * class or a collection and returns the same.
     *
     * @param array $data
     *
     * @return Entity
     */
    protected static function buildEntity($data, ?string $entityName = null)
    {
        $entity = new static;

        if (! is_null($entityName)) {
            if (in_array($name = Str::of($entityName)->singular(), static::getDefinedEntitiesArray())) {
                $name = static::resolveEntityName($name);
                $class = static::getEntityClass($name);
                $entity = new $class;
            }
        }
        
        if (is_array($data)) {
            $entity->fill($data);
        } else {
            $entity->response = $data;
        }

        return $entity;
        //if (isset($data['entity'])) {
        //     if (in_array($data['entity'], $entities)) {
        //         //$class = static::getEntityClass($data['entity']);
        //         $entity = new $class;
        //     } else {
        //         $entity = new static;
        //     }
        // } else {
        //     $entity = new static;
        // }
    }

    public function fill($data)
    {
        $attributes = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && count($value) > 0) {
                if (static::isAssocArray($value)) {
                    $value = static::buildEntity($value, $key);
                } else {
                    $collection = [];
                    
                    foreach ($value as $entity) {
                        $model = static::buildEntity($entity, $key);
                        array_push($collection, $model);
                    }
                    // foreach ($value as $v) {
                    //     if (is_array($v)) {
                    //         $entity = static::buildEntity($v);
                    //         array_push($collection, $entity);
                    //     } else {
                    //         array_push($collection, $v);
                    //     }
                    // }
                    $value = $collection;
                }
            }
            
            $attributes[$key] = $value;
        }

        $this->attributes = $attributes;
    }

    /**
     * @param array $params
     *
     * @return \Ezyslips\Api\Entity
     */
    protected function fetch(array $params = [])
    {
        $entityUrl = $this->getEntityUrl();

        return $this->request('GET', $entityUrl, $params, true);
    }

    protected function all(array $options = [])
    {
        $entityUrl = $this->getEntityUrl();

        return $this->request('GET', $entityUrl, $options);
    }

    protected function create(array $attributes = [])
    {
        $entityUrl = $this->getEntityUrl();

        return $this->request('POST', $entityUrl, $attributes);
    }
}
