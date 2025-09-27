<?php

namespace Itb\Core\Traits;

trait Resourceble
{
    public final function __get(string $property) : mixed
    {
        return $this->resource[$property] ?? null;
    }

    public final function __set(string $property, mixed $value) : void
    {
        $this->resource[$property] = $value;
    }

    public final function __unset(string $property) : void
    {
        unset($this->resource[$property]);
    }

    public final function __isset(string $property) : bool
    {
        return isset($this->resource[$property]);
    }

    public function offsetSet(mixed $offset, mixed $value) : void 
    {
        if (is_null($offset)) {
            $this->resource[] = $value;
        } else {
            $this->resource[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset) : bool 
    {
        return isset($this->resource[$offset]);
    }

    public function offsetUnset(mixed $offset) : void 
    {
        unset($this->resource[$offset]);
    }

    public function offsetGet(mixed $offset) : mixed 
    {
        return isset($this->resource[$offset]) ? $this->resource[$offset] : null;
    }

    public function count() : int
    {
        return count($this->resource);
    }
    
    public function jsonSerialize(): mixed
    {
        return $this->resource;
    }
}