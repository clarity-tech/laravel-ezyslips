<?php

namespace ClarityTech\Ezyslips\Api;

use ClarityTech\Ezyslips\Exceptions\EzyslipsApiException;
use Illuminate\Contracts\Support\Arrayable;

class EzyslipsResponse implements Arrayable
{
    public bool $success = false;
    public bool $error = true;
    public ?string $message = null;

    public function hasErrors() : bool
    {
        return ($this->error && !$this->success);
    }

    public function isSimple() : bool
    {
        return ! is_null($this->message);
    }

    public static function validateAndParseIfSimple(array $response)
    {
        $entity = static::parseSimple($response);

        if ($entity->hasErrors()) {
            $entity->raiseError();
        }

        return $entity;
    }

    public function raiseError()
    {
        throw new EzyslipsApiException($this->message?? 'Something went wrong!');
    }

    public static function parseSimple(array $response)
    {
        $entity = new static;

        $entity->parseResponseKeys($response);

        if (is_string($message = $response['message']?? null)) {
            $entity->message = $message;
        }

        return $entity;
    }

    public function parseResponseKeys(array $response)
    {
        foreach (['success', 'error',] as $key) {
            if (in_array($key, array_keys($response))) {
                $this->{$key} = (bool) $response[$key];
            }
        }
    }

    public function toArray()
    {
        $array = [
            'success' => $this->success,
            'error' => $this->error,
            'message' => $this->message
        ];

        return $array;
    }
}
