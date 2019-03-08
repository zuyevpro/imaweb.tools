<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$moduleName = 'imaweb.tools';

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Imaweb\Tools\Migration;

global $APPLICATION;

/**
 * @global $action
 */

Loader::includeModule($moduleName);

Loc::loadLanguageFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight($moduleName);
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "table_migrations_list";
$oSort = new CAdminSorting($sTableID, "date", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$actionResult = null;
$isAction = false;
$debugInfo = "";
if (!is_null($action))
{
	ob_start();

	$isAction = true;
	switch ($action)
	{
		case 'new':
		{
			$result = Migration::add(htmlspecialchars($_POST['name']));
			if (!$result)
			{
				$actionResult = array(
					'status' => false,
					'message' => Migration::getLastError(),
				);
			}
			else
			{
				$actionResult = array(
					'status' => true,
				);
			}
			break;
		}
		case 'run':
		{
			$result = Migration::run(!isset($_REQUEST['rollback']));
			if (!$result)
			{
				$actionResult = array(
					'status' => false,
					'message' => Migration::getLastError(),
				);
			}
			else
			{
				$actionResult = array(
					'status' => true,
				);
			}
			
			break;
		}
		case 'clear': {
			$result = Migration::clear();
			if (!$result)
			{
				$actionResult = array(
					'status' => false,
					'message' => Migration::getLastError(),
				);
			}
			else
			{
				$actionResult = array(
					'status' => true,
				);
			}
		}
	}

	$debugInfo = ob_get_contents();
	ob_end_clean();
}

$arData = Migration::getList();
usort($arData, function($a, $b)
{
	return $a < $b ? 1 : -1;
});

$lAdmin->AddHeaders(array(
	array(
		'id' => 'date',
		'content' => Loc::getMessage('COLUMN_DATE'),
		'default' => true,
	),
	array(
		'id' => 'name',
		'content' => Loc::getMessage('COLUMN_NAME'),
		'default' => true,
	),
	array(
		'id' => 'applied',
		'content' => Loc::getMessage('COLUMN_APPLIED'),
		'default' => true,
	),
));

foreach ($arData as $arItem)
{
	$row = &$lAdmin->AddRow($arItem['id'], $arItem);

	$row->AddField('date', $arItem['date']);
	$row->AddField('name', $arItem['name']);
	$row->AddField('applied', $arItem['applied'] ? Loc::getMessage('YES') : ' ');

}

$lAdmin->AddFooter(
	array(
		array(
			'title' => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			'value' => count($arData)
		),
		array(
			'counter' => false,
			'title' => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			'value' => '0',
		),
	)
);

// Верхнее меню из кнопок
$listTopButtons = array(
	array(
		'TEXT' => Loc::getMessage('MIGRATION_ADD'),
		'LINK' => 'javascript:getNewMigrationWindow();',
		'TITLE' => Loc::getMessage('MIGRATION_ADD'),
		'ICON' => 'btn_new',
	),
	array(
		'TEXT' => Loc::getMessage('MIGRATION_RUN'),
		'LINK' => 'javascript:runMigrations();',
		'TITLE' => Loc::getMessage('MIGRATION_RUN'),
	),
	array(
		'TEXT' => Loc::getMessage('MIGRATION_ROLLBACK'),
		'LINK' => 'javascript:rollbackMigrations();',
		'TITLE' => Loc::getMessage('MIGRATION_ROLLBACK'),
	),
	array(
		'TEXT' => Loc::getMessage('MIGRATION_CLEAR_OLD'),
		'LINK' => 'javascript:clearAppliedMigrations();',
		'TITLE' => Loc::getMessage('MIGRATION_CLEAR_OLD'),
	),
);

$lAdmin->AddAdminContextMenu($listTopButtons, false, false);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('MIGRATION_TITLE'));

CJSCore::Init(array("jquery"));

$path = getLocalPath('modules/imaweb.tools/js/admin_migrations.js');
Bitrix\Main\Page\Asset::getInstance()->addJs($path);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($isAction)
{
	$APPLICATION->RestartBuffer();
	header('Content-Type: application/json');
	echo json_encode($actionResult);
	die();
}

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");