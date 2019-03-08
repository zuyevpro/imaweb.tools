<?
global $MESS;
IncludeModuleLangFile(__FILE__);

if (class_exists('imaweb_tools')) return;
class imaweb_tools extends CModule
{
	var $MODULE_ID = 'imaweb.tools';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $PARTNER_NAME;
	var $PARTNER_URI;

	var $errors;

	function imaweb_tools()
	{
		$arModuleVersion = array();

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path . '/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = GetMessage('IMAWEB_TOOLS_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('IMAWEB_TOOLS_MODULE_DESCRIPTION');
		$this->PARTNER_NAME = GetMessage('IMAWEB_TOOLS_MODULE_DEVELOPER');
		$this->PARTNER_URI = GetMessage('IMAWEB_TOOLS_MODULE_DEVELOPER_URI');
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;
		$this->errors = false;

		RegisterModule($this->MODULE_ID);
		CModule::IncludeModule($this->MODULE_ID);

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModule($this->MODULE_ID);

		return true;
	}

	function InstallEvents()
	{
		$em = \Bitrix\Main\EventManager::getInstance();

		$em->registerEventHandlerCompatible('main', 'OnEpilog', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "AddCustomScriptOrStyle");
		$em->registerEventHandlerCompatible('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "CheckReCaptcha");
		$em->registerEventHandlerCompatible('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "defineIblockConstants");
		$em->registerEventHandlerCompatible('iblock', 'OnBeforeIBlockAdd', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "SetDefaultIblockRights");
		return true;
	}

	function UnInstallEvents()
	{
		$em = \Bitrix\Main\EventManager::getInstance();

		$em->unRegisterEventHandler('main', 'OnEpilog', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "AddCustomScriptOrStyle");
		$em->unRegisterEventHandler('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "CheckReCaptcha");
		$em->unRegisterEventHandler('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "defineIblockConstants");
		$em->unRegisterEventHandler('iblock', 'OnBeforeIBlockAdd', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "SetDefaultIblockRights");
		return true;
	}

	function InstallFiles()
	{
		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $DB, $APPLICATION, $step;
		$step = IntVal($step);
		if ($step < 2)
		{
			$GLOBALS['install_step'] = 1;

			$path = getLocalPath('modules/' . $this->MODULE_ID . '/install/step.php');
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path))
			{
				$APPLICATION->IncludeAdminFile(GetMessage('IMAWEB_TOOLS_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . $path);
			}
		}
		elseif ($step == 2)
		{

			if ($this->InstallDB())
			{
				$this->InstallEvents();
				$this->InstallFiles();
			}
			$GLOBALS['errors'] = $this->errors;
			$GLOBALS['install_step'] = 2;
			$path = getLocalPath('modules/' . $this->MODULE_ID . '/install/step.php');
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path))
			{
				$APPLICATION->IncludeAdminFile(GetMessage('IMAWEB_TOOLS_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . $path);
			}
		}
	}

	function DoUninstall()
	{
		global $DB, $APPLICATION, $step;

		if ($step < 2)
		{
			$GLOBALS['uninstall_step'] = 1;
			$path = getLocalPath('modules/' . $this->MODULE_ID . '/install/unstep.php');
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path))
			{
				$APPLICATION->IncludeAdminFile(GetMessage('IMAWEB_TOOLS_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . $path);
			}
		}
		elseif ($step == 2)
		{

			$this->UnInstallDB(array(
				'savedata' => ($_REQUEST['savedata'] == 'Y'),
			));

			$this->UnInstallEvents();
			$this->UnInstallFiles();
			$GLOBALS['errors'] = $this->errors;
			$GLOBALS['uninstall_step'] = 2;
			$path = getLocalPath('modules/' . $this->MODULE_ID . '/install/unstep.php');
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path))
			{
				$APPLICATION->IncludeAdminFile(GetMessage('IMAWEB_TOOLS_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . $path);
			}
		}
	}
}
?>
