<?php

namespace Itb\Core\Helpers;

class WebHelper
{
    public static function getUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($data), 4)
        );
    }

    public static function generateCode($length = 6): string
    {
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
        //return mt_rand(100000, 999999);
    }

    public static function jsonAnswer(array $result)
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json');
        echo \Bitrix\Main\Web\Json::encode($result);
        \CMain::FinalActions();
    }
}
