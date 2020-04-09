<?
use Bitrix\Main\EventManager;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentNullException;

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
        if ($this->InstallDB())
        {
            $this->InstallEvents();
            $this->InstallFiles();

            $key = RandString(64);

            try {
                Option::set($this->MODULE_ID, 'update_key', $key);
                $this->hookInstall($key);
            }
            catch (ArgumentOutOfRangeException $e) {

            }
        }
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

        $this->UnInstallDB(array(
            'savedata' => ($_REQUEST['savedata'] == 'Y'),
        ));

        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $GLOBALS['errors'] = $this->errors;

        try {
            $key = Option::get($this->MODULE_ID, 'update_key', '');
            $this->hookUninstall($key);
        }
        catch (ArgumentNullException $e) {

        }
        catch (ArgumentOutOfRangeException $e) {

        }

	}

	function hookInstall($key) {
	    $client = new HttpClient([
            'redirect' => true, // true, если нужно выполнять редиректы
            'redirectMax' => 1, // Максимальное количество редиректов
            'waitResponse' => true, // true - ждать ответа, false - отключаться после запроса
            'socketTimeout' => 30, // Таймаут соединения, сек
            'streamTimeout' => 60, // Таймаут чтения ответа, сек, 0 - без таймаута
            'version' => HttpClient::HTTP_1_1, // версия HTTP (HttpClient::HTTP_1_0 или HttpClient::HTTP_1_1)
        ]);

	    $client->post('https://zuyev.pro/api/v1/install/imaweb.tools', [
	        'host' => $_SERVER['HTTP_HOST'],
            'key' => $key,
        ]);
    }

    function hookUninstall($key) {
	    $client = new HttpClient([
            'redirect' => true, // true, если нужно выполнять редиректы
            'redirectMax' => 1, // Максимальное количество редиректов
            'waitResponse' => true, // true - ждать ответа, false - отключаться после запроса
            'socketTimeout' => 30, // Таймаут соединения, сек
            'streamTimeout' => 60, // Таймаут чтения ответа, сек, 0 - без таймаута
            'version' => HttpClient::HTTP_1_1, // версия HTTP (HttpClient::HTTP_1_0 или HttpClient::HTTP_1_1)
        ]);

	    $client->post('https://zuyev.pro/api/v1/install/imaweb.tools', [
	        'host' => $_SERVER['HTTP_HOST'],
            'key' => $key,
            'uninstall' => 'Y',
        ]);
    }


}