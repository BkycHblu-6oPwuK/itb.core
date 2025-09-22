<?php

namespace Itb\Core\Helpers;

use Bitrix\Main\Loader;

Loader::IncludeModule("sale");

class LocationHelper
{
    private function __construct() {}
    
    /**
     * Поиск по CSaleLocation
     */
    public static function get($filter = [], $select = ['*'], bool $returnFirst = false)
    {
        $locations = [];
        if ($returnFirst) {
            $filter['nTopCount'] = 1;
        }
        $dbLocationList = \CSaleLocation::GetList(
            ["SORT" => "ASC"],
            $filter,
            false,
            false,
            $select
        );

        if ($returnFirst) {
            return $dbLocationList->Fetch() ?: [];
        }

        while ($arLocation = $dbLocationList->Fetch()) {
            $locations[] = $arLocation;
        }

        return $locations;
    }

    public static function getCityNameByCityCode(string $cityCode): string
    {
        $locationInfo = '';

        $filter = ["LID" => LANGUAGE_ID, "CODE" => $cityCode];
        $arLocation = static::get($filter, ["CITY_NAME"]);

        if (!empty($arLocation)) {
            $locationInfo = array_shift($arLocation)['CITY_NAME'];
        }

        return $locationInfo;
    }

    public static function getAllLocations()
    {
        return static::get();
    }

    public static function getAllCities()
    {
        return static::get(['CITY_LID' => 'ru'], ["CITY_NAME", 'ID']);
    }

    public static function getLocationInfoByCityName($cityName)
    {
        $locationInfo = [];

        $filter = ["LID" => LANGUAGE_ID, "CITY_NAME" => $cityName];
        $arLocation = static::get($filter, ["ID", "CITY_ID", "REGION_ID", "COUNTRY_ID"]);
        foreach ($arLocation as $location) {
            $locationInfo[] = $location["ID"];
            $locationInfo[] = $location["CITY_ID"];
            $locationInfo[] = $location["REGION_ID"];
            $locationInfo[] = $location["COUNTRY_ID"];
        }

        return array_unique($locationInfo);
    }

    /**
     * поиск через компонент sale.location.selector.search
     */
    public static function find(string $query, int $pageSize = 50, $page = 0)
    {
        \CBitrixComponent::includeComponentClass('bitrix:sale.location.selector.search');
        $parameters = [
            'select' => ['CODE', 'TYPE_ID', 'VALUE' => 'ID', 'DISPLAY' => 'NAME.NAME'],
            'additionals' => ['PATH'],
            'filter' => ['=PHRASE' => $query, '=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=SITE_ID' => SITE_ID],
            'PAGE_SIZE' => $pageSize,
            'PAGE' => $page,
        ];
        $data = \CBitrixLocationSelectorSearchComponent::processSearchRequestV2($parameters);
        $pathItems = $data['ETC']['PATH_ITEMS'];
        foreach($data['ITEMS'] as &$item) {
            foreach($item['PATH'] as $keyPath => &$path) {
                if($pathItems[$path]) {
                    $path = $pathItems[$path];
                } else {
                    unset($item['PATH'][$keyPath]);
                }
            }
        }
        return $data['ITEMS'];
    }
}
