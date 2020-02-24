<?php
namespace Imaweb\Tools;

use \Bitrix\Main\Config\Option,
	Bitrix\Main\Loader,
	\Bitrix\Main\Page\Asset,
	\Bitrix\Main\Web\HttpClient,
	\Bitrix\Iblock\IblockTable;

abstract class Handlers
{
	public static function AddCustomScriptOrStyle()
	{
		if (Option::get('imaweb.tools', 'custom_js_css', 'N') == 'Y')
		{
			$curDir = $_SERVER['REAL_FILE_PATH'];
			if (strlen($curDir) > 0)
			{
				$curDir = dirname($curDir) . '/';
			}
			else
			{
				$curDir = $GLOBALS["APPLICATION"]->GetCurDir();
			}

			if (file_exists($_SERVER['DOCUMENT_ROOT'] . $curDir . 'style.css'))
			{
				Asset::getInstance()->addCss($curDir . 'style.js');
			}

			if (file_exists($_SERVER['DOCUMENT_ROOT'] . $curDir . 'script.js'))
			{
				Asset::getInstance()->addJs($curDir . 'script.js');
			}
		}
	}

	public static function CheckReCaptcha()
	{
		if (!defined('ADMIN_SECTION'))
		{
			$checked = false;
			if (Option::get('imaweb.tools', 'gre_on', 'N') == 'Y')
			{
				if (strlen($_REQUEST["g-recaptcha-response"]) > 0)
				{
					$ip = $_SERVER['REMOTE_ADDR'];
					if (array_key_exists('HTTP_X_REAL_IP', $_SERVER))
					{
						if (strlen($_SERVER['HTTP_X_REAL_IP']) > 0)
						{
							$ip = $_SERVER['HTTP_X_REAL_IP'];
						}
					}

					$client = new HttpClient();

					$response = $client->post('https://www.google.com/recaptcha/api/siteverify', array(
						'secret' => Option::get('imaweb.tools', 'gre_secret'),
						'response' => htmlspecialchars($_REQUEST['g-recaptcha-response']),
						'remoteip' => $ip,
					));

					if (strlen($response) > 0)
					{
						$response = json_decode($response, true);
						if (is_array($response))
						{
							$checked = $response['success'] === true;
						}
					}
				}
			}
			else
			{
				$checked = true;
			}

			define("RECAPTCHA_CHECKED", $checked);
		}
	}

    function SetDefaultIblockRights(&$arParams)
    {
        if (Option::get('imaweb.tools', 'set_default_iblock_rights', 'N') == 'Y')
        {
            $arParams['GROUP_ID'][2] = "R";
        }
    }

    public static function defineIblockConstants()
    {
        if (Loader::includeModule('iblock'))
        {
            $result = IblockTable::getList(array(
                'select' => array('ID', 'IBLOCK_TYPE_ID', 'CODE'),
            ));
            while ($row = $result->fetch())
            {
                $CONSTANT = ToUpper(implode('_', array('IBLOCK', $row['IBLOCK_TYPE_ID'])));
                if (!defined($CONSTANT))
                {
                    define($CONSTANT, $row['ID']);
                }

	            $CONSTANT = ToUpper(implode('_', array('IBLOCK', $row['IBLOCK_TYPE_ID'], $row['CODE'])));
	            if (!defined($CONSTANT))
	            {
		            define($CONSTANT, $row['ID']);
	            }
            }
        }
    }

	public static function defineWebFormConstants()
	{
		if (Loader::includeModule('form'))
		{
			$by = 'id';
			$order = 'asc';
			$isFiltered = false;
			$res = \CForm::GetList($by, $order, array(), $isFiltered);
			while ($r = $res->GetNext())
			{
				$CONSTANT = ToUpper(implode('_', array('WEBFORM', $r['SID'])));
				if (!defined($CONSTANT))
				{
					define($CONSTANT, $r['ID']);
				}
			}
		}
	}

    function captchaChecking($WEB_FORM_ID, &$arFields, &$arrVALUES)
    {
        if (Option::get('imaweb.tools', 'gre_on') !== 'Y')
        {
            return;
        }

        global $APPLICATION;

        if (defined('RECAPTCHA_CHECKED') && !RECAPTCHA_CHECKED)
        {
            $APPLICATION->ThrowException('Проверка reCaptcha не пройдена');
        }
    }
}