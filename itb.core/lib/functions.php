<?php
/**
 * @throws InvalidArgumentException если @param $throw === true и значение переменной false
 */
function getEnvVar(string $varName, bool $throw = true)
{
    $value = getenv($varName);
    if ($throw && $value === false) {
        throw new \InvalidArgumentException("Environment variable '{$varName}' is not set or is empty.");
    }
    return $value;
}