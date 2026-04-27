<?php

namespace App\Infrastructure\Handler\Response;

use JsonSerializable;

class ResponseTest implements JsonSerializable
{
    private array $response;

    /**
     * @param array $response
     */
    private function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    public function jsonSerialize(): mixed
    {
        return $this->response;
    }



    public static function setResponse(mixed $response): ResponseTest
    {
        return new self($response);
    }
}
