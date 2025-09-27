<?

class itb_core extends CModule
{
    var $MODULE_ID = 'itb.core';
    var $MODULE_NAME = 'itb.core';
    var $MODULE_DESCRIPTION = "itb.core";
    var $MODULE_VERSION = "1.0";
    var $MODULE_VERSION_DATE = "2024-04-09 12:00:00";
    var $PARTNER_NAME = 'itb.core';
    var $PARTNER_URI = 'itb.core';

    public function DoInstall()
    {
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}