<?php
namespace Itb\Core\Http\Resources;

use Itb\Core\Traits\Resourceble;

abstract class Resource implements ResourceInterface, \JsonSerializable, \ArrayAccess, \Countable
{
    use Resourceble;

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