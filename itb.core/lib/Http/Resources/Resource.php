<?php
namespace Itb\Core\Http\Resources;

abstract class Resource implements ResourceInterface, \JsonSerializable, \ArrayAccess, \Countable
{
    use ResourceTrait;

    private $resource;

    private function __construct(array $data)
    {
        $this->resource = $data;
    }

    public final static function make(array $data) : static
    {
        return new static($data);
    }
}