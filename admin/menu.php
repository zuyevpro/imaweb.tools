<?

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

if (Loader::includeModule('imaweb.tools') && Option::get('imaweb.tools', 'show_migrations_in_menu') == 'Y') {
    if ($APPLICATION->GetGroupRight("imaweb.tools") != "D") {
        Loc::loadLanguageFile(__FILE__);

        $aMenu = array(
            "parent_menu" => "global_menu_settings",
            "sort" => 1,
            "text" => Loc::getMessage("IMAWEB_TOOLS_MIGRATIONS"),
            "title" => Loc::getMessage("IMAWEB_TOOLS_MIGRATIONS_TITLE"),
            "url" => "imaweb_tools_migrations.php?lang=" . LANGUAGE_ID,
            "icon" => "update_menu_icon_partner",
        );

        return $aMenu;
    }

    return false;
}