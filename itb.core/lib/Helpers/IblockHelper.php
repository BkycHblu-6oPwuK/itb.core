<?php

namespace Itb\Core\Helpers;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Loader;
use Bitrix\Iblock\PropertyTable;

class IblockHelper
{
    static $iblockCodeIdMap = [];

    /**
     * Получает id инфоблока по его коду
     *
     * @param string $iblockCode
     *
     * @return int
     */
    public static function getIblockIdByCode(string $iblockCode): int
    {
        if (!isset(static::$iblockCodeIdMap[$iblockCode])) {
            Loader::includeModule('iblock');

            $id = IblockTable::getList([
                'select' => ['ID'],
                'filter' => ['CODE' => $iblockCode],
                'cache' => ['ttl' => 86400]
            ])->fetch()['ID'];

            if (!$id) {
                throw new \Exception("Iblock with code {$iblockCode} not found");
            }

            static::$iblockCodeIdMap[$iblockCode] = $id;
        }

        return static::$iblockCodeIdMap[$iblockCode];
    }

    /**
     * @param $code
     * @param $iblockId
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public static function getIblockPropIdByCode(string $code, int $iblockId): int
    {
        $propId = PropertyTable::query()
        ->setSelect(['ID'])
        ->where('IBLOCK_ID', $iblockId)
        ->where('CODE',$code)
        ->setCacheTtl(86400)
        ->exec()
        ->fetch()['ID'];
        return $propId ? (int)$propId : 0;
    }

    /**
     * @param int   $propId
     * @param array $xmlIds
     *
     * @return array [ xmlId => [id => valueId] ]
     */
    public static function getEnumValues(int $propId, array $xmlIds = []): array
    {
        $dbRes = \CIBlockPropertyEnum::GetList([], [
            'PROPERTY_ID' => $propId,
            'XML_ID'      => $xmlIds
        ]);

        $values = [];
        while ($value = $dbRes->Fetch()) {
            $values[$value['XML_ID']] = [
                'id' => $value['ID']
            ];
        }

        return $values;
    }

}
