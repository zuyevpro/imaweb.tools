<?
use \Bitrix\Main\EventManager;

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

	function __construct()
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
//		global $DB, $APPLICATION;
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
		$em = EventManager::getInstance();

		$em->registerEventHandlerCompatible('main', 'OnEpilog', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "AddCustomScriptOrStyle");
		$em->registerEventHandlerCompatible('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "CheckReCaptcha");
		$em->registerEventHandlerCompatible('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "defineIblockConstants");
		$em->registerEventHandlerCompatible('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "defineWebFormConstants");
		$em->registerEventHandlerCompatible('iblock', 'OnBeforeIBlockAdd', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "SetDefaultIblockRights");
		$em->registerEventHandlerCompatible('form', 'onBeforeResultAdd', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "captchaChecking");
		return true;
	}

	function UnInstallEvents()
	{
		$em = EventManager::getInstance();

		$em->unRegisterEventHandler('main', 'OnEpilog', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "AddCustomScriptOrStyle");
		$em->unRegisterEventHandler('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "CheckReCaptcha");
		$em->unRegisterEventHandler('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "defineIblockConstants");
		$em->unRegisterEventHandler('main', 'OnPageStart', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "defineWebFormConstants");
		$em->unRegisterEventHandler('iblock', 'OnBeforeIBlockAdd', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "SetDefaultIblockRights");
        $em->unRegisterEventHandler('form', 'onBeforeResultAdd', 'imaweb.tools', "\\Imaweb\\Tools\\Handlers", "captchaChecking");
		return true;
	}

	private function getDirPath($path, $noDocRoot = false)
	{
		$path2 = $_SERVER["DOCUMENT_ROOT"]."/local/" . $path;
		if (!is_dir($path2))
		{
			var_dump($path2);
			$path2 = $_SERVER["DOCUMENT_ROOT"]."/bitrix/" . $path;

			if ($noDocRoot)
			{
				return 'bitrix/' . $path;
			}

			return $path2;
		}

		if (!is_dir($path2))
		{
			return false;
		}

		if ($noDocRoot)
		{
			return 'local/' . $path;
		}

		return $path2;
	}

	function InstallFiles()
	{
		// components
		$path = $this->getDirPath("modules/" . $this->MODULE_ID . "/install/components");
		if ($path === false)
		{
			return false;
		}
		CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"]."/local/components/imaweb", true, true);

		// admin scripts
		$path = $this->getDirPath("modules/" . $this->MODULE_ID . "/install/admin");
		if ($path === false)
		{
			return false;
		}
		CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);


		return true;
	}

	function UnInstallFiles()
	{
		// components
		$path = $this->getDirPath("components/imaweb/social.links", true);
		DeleteDirFilesEx($path);

		// admin scripts
		$path = $this->getDirPath("modules/" . $this->MODULE_ID . "/install/admin");
		if ($path === false)
		{
			return false;
		}
		DeleteDirFiles($path, $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;
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
		global $APPLICATION, $step;

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