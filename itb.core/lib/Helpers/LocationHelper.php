<?php

namespace Itb\Core\Helpers;

use Bitrix\Main\Loader;

class LocationHelper
{
    private function __construct() {}
    
    public static function get($filter = [], $select = ['*'])
    {
        if (!Loader::IncludeModule("sale")) {
            return [];
        }
        $locations = [];
        $dbLocationList = \CSaleLocation::GetList(
            ["SORT" => "ASC"],
            $filter,
            false,
            false,
            $select
        );

        while ($arLocation = $dbLocationList->Fetch()) {
            $locations[] = $arLocation;
        }

        return $locations;
    }

    public static function getCityNameByCityCode(string $cityCode) : string
    {
        $locationInfo = '';

        if (!Loader::IncludeModule("sale")) {
            return $locationInfo;
        }

        $filter = ["LID" => LANGUAGE_ID, "CODE" => $cityCode];
        $arLocation = static::get($filter, ["CITY_NAME"]);

        if(!empty($arLocation)){
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
        return static::get(['CITY_LID' => 'ru'],["CITY_NAME",'ID']);
    }

    public static function getLocationInfoByCityName($cityName)
    {
        $locationInfo = [];

        if (!Loader::IncludeModule("sale")) {
            return $locationInfo;
        }

        $filter = ["LID" => LANGUAGE_ID, "CITY_NAME" => $cityName];
        $arLocation = static::get($filter, ["ID", "CITY_ID", "REGION_ID", "COUNTRY_ID"]);
        foreach($arLocation as $location){
            $locationInfo[] = $location["ID"];
            $locationInfo[] = $location["CITY_ID"]; 
            $locationInfo[] = $location["REGION_ID"];
            $locationInfo[] = $location["COUNTRY_ID"]; 
        }

        return array_unique($locationInfo);
    }
}
