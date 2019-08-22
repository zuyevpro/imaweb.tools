<?
use Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);
if (Loader::includeModule('imaweb.tools') && Option::get('main', 'update_devsrv') == 'Y')
{
	if($APPLICATION->GetGroupRight("imaweb.tools") != "D")
	{
		Loc::loadLanguageFile(__FILE__);

		$aMenu = array(
			"parent_menu" => "global_menu_settings",
			"sort" => 1,
			"text" => Loc::getMessage("IMAWEB_TOOLS_MIGRATIONS"),
			"title" => Loc::getMessage("IMAWEB_TOOLS_MIGRATIONS_TITLE"),
			"url" => "scid_tools_migrations.php?lang=" . LANGUAGE_ID,
			"icon" => "update_menu_icon_partner",
		);

		return $aMenu;
	}

	return false;
}