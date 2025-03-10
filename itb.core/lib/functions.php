<?php
/**
 * @throws InvalidArgumentException если @param $default null и @param $throw true, а значение переменной false
 */
function getEnvVar(string $varName, mixed $default = null, bool $throw = true)
{
    $value = getenv($varName);
    
    if ($value === false) {
        if ($throw && $default === null) {
            throw new \InvalidArgumentException("Environment variable '{$varName}' is not set or is empty.");
        }
        $value = $default;
    }

    return $value;
}