<?php
namespace Itb\Core\Http\Resources;

interface ResourceInterface
{
    public static function make(array $data) : static;
    public function toArray() : array;
}