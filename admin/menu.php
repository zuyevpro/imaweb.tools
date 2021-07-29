<?

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

if (Loader::includeModule('imaweb.tools') && $APPLICATION->GetGroupRight("imaweb.tools") != "D") {

    Loc::loadLanguageFile(__FILE__);

    $aMenu = [];

    if (Option::get('imaweb.tools', 'show_migrations_in_menu') == 'Y') {
        $aMenu[] = [
            "parent_menu" => "global_menu_settings",
            "sort" => 1,
            "text" => Loc::getMessage("IMAWEB_TOOLS_MIGRATIONS"),
            "title" => Loc::getMessage("IMAWEB_TOOLS_MIGRATIONS_TITLE"),
            "url" => "imaweb_tools_migrations.php?lang=" . LANGUAGE_ID,
            "icon" => "update_menu_icon_partner",
        ];
    }

    $aMenu[] = [
        "parent_menu" => "global_menu_content",
        "sort" => 2,
        "text" => Loc::getMessage("IMAWEB_TOOLS_REDIRECTS"),
        "title" => Loc::getMessage("IMAWEB_TOOLS_REDIRECTS_TITLE"),
        "url" => "imaweb_tools_redirects.php?lang=" . LANGUAGE_ID,
        "icon" => "util_menu_icon",
        "more_url" => [
            '/bitrix/admin/imaweb_tools_redirect_edit.php',
            '/bitrix/admin/imaweb_tools_redirects_import.php',
        ],
    ];

    return $aMenu;
}

