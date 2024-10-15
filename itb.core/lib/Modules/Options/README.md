Классы помошники для создания полей настроек в модуле.

Можно создавать табы и в них прокидывать поля.

Для каждого типа поля который обрабатывает битрикс (в bitrix/modules/main/admin/settings.php функция renderInput) был реализован класс.

## Пример

```php

$mainTab = new Tab("edit1", "Название вкладки в табах", "Главное название в админке");
$mainTab->addField((new Checkbox("hmarketing_checkbox1", "Поясняющий текс элемента checkbox"))->setLabel("Название секции checkbox"))
->addField((new Input("hmarketing_text", "Поясняющий текс элемента text"))->setSize(10)->setDefaultValue("Жми!"));

$mainTab->addField((new Password("hmarketing_Password", "Поясняющий текс элемента Password"))->setLabel('Название секции Password')->setDefaultValue("Password"));
$mainTab->addField(new StaticText("Поясняющий текс элемента StaticText", "StaticText"));
$mainTab->addField(new StaticHtml("Поясняющий текс элемента StaticHtml", "<a href='1221'>StaticHtml</a>"));
$mainTab->addField((new TextArea("hmarketing_text3", "Поясняющий текс элемента text"))->setSize([10,50])->setDefaultValue("Жми!"));
$mainTab->addField((new Input("hmarketing_text_dis", "Поясняющий текс элемента text dis", '10'))->setLabel('Название секции text dis')->setDefaultValue("Жми! dis")->isDisabled());
$mainTab->addField((new Select("hmarketing_selectbox", "Поясняющий текс элемента selectbox", [
    "460" => "460Х306",
    "360" => "360Х242"
]))->setDefaultValue("460"));
$mainTab->addField((new Select("hmarketing_selectbox dis", "Поясняющий текс элемента selectbox dus", [
    "460" => "460Х306",
    "360" => "360Х242"
]))->isDisabled()->setDefaultValue("460"));
$mainTab->addField((new MultiSelect("MultiSelect" ,"Поясняющий текс элемента multiselectbox", [
    "left" => "Лево",
    "right" => "Право",
    "top" => "Верх",
    "bottom" => "Низ"
]))->setDefaultValue(['left','bottom']));

$tabsBuilder = new TabsBuilder();
$tabsBuilder->addTab($mainTab);

$tabs = $tabsBuilder->getTabs();

if ($request->isPost() && check_bitrix_sessid()) {
    foreach ($tabs as $tab) {
        $fileds = $tab->getFields();
        if (!isset($fileds)) {
            continue;
        }
        foreach ($fileds as $filed) {
            if($name = $filed->getName()){
                if ($request["apply"]) {
                    $optionValue = $request->getPost($name);
                    $optionValue = is_array($optionValue) ? implode(",", $optionValue) : $optionValue;
                    Option::set($module_id, $name, $optionValue);
                }
                if ($request["default"]) {
                    Option::set($module_id, $name, $filed->getDefaultValue());
                }
            }
        }
    }
}

$tabControl = new CAdminTabControl(
    "tabControl",
    $tabsBuilder->getTabsFormattedArray()
);

$tabControl->Begin();

?>
<form action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= $module_id ?>&lang=<?= LANG ?>" method="post">
    <? foreach ($tabs as $tab) {
        if ($options = $tab->getOptionsFormattedArray()) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $options);
        }
    }
    $tabControl->BeginNextTab();

    require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php";

    $tabControl->Buttons();
    echo (bitrix_sessid_post());
    ?>
    <input class="adm-btn-save" type="submit" name="apply" value="Применить" />
    <input type="submit" name="default" value="По умолчанию" />
</form>
<?
$tabControl->End();
```