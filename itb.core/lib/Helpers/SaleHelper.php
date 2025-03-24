<?php

namespace Itb\Core\Helpers;

use Bitrix\Sale\Internals\StatusTable;

class SaleHelper
{
    public static function getStatuses(): array
    {
        return StatusTable::query()->setSelect(['ID', 'NAME' => 'STATUS_LANG.NAME'])->where('STATUS_LANG.LID', \Bitrix\Main\Localization\Loc::getCurrentLang() ?: 'ru')->setCacheTtl(86400)->cacheJoins(true)->fetchAll();
    }

    public static function getActivePayment()
    {
        return \Bitrix\Sale\PaySystem\Manager::getList(['filter' => ['ACTIVE' => 'Y']])->fetchAll();
    }

    public static function getActiveDelivery()
    {
        return \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
    }
}
