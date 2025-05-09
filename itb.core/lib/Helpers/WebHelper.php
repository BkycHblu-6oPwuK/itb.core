<?php

namespace Itb\Core\Helpers;

class WebHelper
{
    public static function getUuidV4(): string
    {
        $rquid = bin2hex(random_bytes(16));
        $rquid = sprintf(
            '%08s-%04s-%04x-%04x-%12s',
            substr($rquid, 0, 8),
            substr($rquid, 8, 4),
            (hexdec(substr($rquid, 12, 4)) & 0x0fff) | 0x4000,
            (hexdec(substr($rquid, 16, 4)) & 0x3fff) | 0x8000,
            substr($rquid, 20, 12)
        );

        return $rquid;
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
        echo \Bitrix\Main\Web\Json::encode($result);
        \CMain::FinalActions();
    }
}
