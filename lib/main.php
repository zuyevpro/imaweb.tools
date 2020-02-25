<?php

namespace Imaweb\Tools;

use \Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use \Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use \CIBlockPropertyEnum;
use \CIBlock;
use \CIBlockProperty;
use \CIBlockRights;
use \CIBlockElement;
use \CFile;

abstract class Main
{
    /**
     * Метод склоняет сопутствующее слово к числительному.
     * Объяснить работу функции проще всего на примере.
     * Для получения возможных вариантов: "1 корень, 2 корня, 5 корней"
     * необходимо в $number передать необходимое число, а во второй параметр
     * передать массив
     * @code
     * array("корень", "корня", "корней);
     * @endcode
     * @param $number - передаваемое число,
     * @param $titles - массив возможных значений со значениями "один", "два", "много".
     * @return string
     */
    public static function declOfNum($number, $titles)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        return $number . " " . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }

    /**
     * Дамп переменной на экран.
     * @param $ar - входная переменная
     * @param $var_dump - необязательный параметр. Определяет способ вывода на экран. По умолчанию используется функция print_r(), если указать в значение true, то будет вызываться функция var_dump().
     * @sa http://php.net/manual/ru/function.print-r.php
     * @sa http://php.net/manual/ru/function.var-dump.php
     * @return void;
     */
    public static function d($ar, $var_dump = false)
    {
        echo '<pre>';
        if ($var_dump)
        {
            var_dump($ar);
        }
        else
        {
            print_r($ar);
        }
        echo '</pre>';
    }

    /**
     * Дамп переменной в файл /x.txt. Для дампа используется функция print_r()
     * @param $var - входная переменная
     * @param $mode - режим записи в выходной файл. По умолчанию 'w+'
     * @sa http://php.net/manual/ru/function.fopen.php
     * @sa http://php.net/manual/ru/function.var-dump.php
     * @return void;
     */
    public static function x($var, $mode = 'w+', $filename = 'x.txt')
    {
        $f = fopen($_SERVER["DOCUMENT_ROOT"] . '/' . $filename, $mode);
        fwrite($f, print_r($var, true));
        fclose($f);
    }

    /**
     * Метод обрезания текста по словам..
     * @param $string - входная строка
     * @param maxlen - максимальная длина выходной строки
     * @return string обрезанная строка
     * @sa http://forum.ugoo.ru/thread-41-post-167.html#pid167
     */
    public static function cutString($string, $maxlen)
    {
        $len = (mb_strlen($string) > $maxlen) ? mb_strripos(mb_substr($string, 0, $maxlen), ' ') : $maxlen;
        $cutStr = mb_substr($string, 0, $len);
        return (mb_strlen($string) > $maxlen) ? $cutStr . '...' : $cutStr;
    }

    /**
     * Обертка для CURL-запроса по произовльному URL-адресу.
     * @param $url - URL-адрес;
     * @param $is_cp1251 - флаг, указывающий, отправлять ли запрос в кодировке 1251. Необязательный параметр. Значение по умолчанию: false.
     * @note По умолчанию запросы отправляются в кодировке utf-8.
     */
    public static function GetContents($url = '', $is_cp1251 = false)
    {
        if (strlen($url) == 0)
        {
            $result = false;
        }
        else
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 Safari/537.11'); //google chrome at 08.01.2013

            if ($is_cp1251)
            {
                $result = iconv('cp1251', 'utf8', curl_exec($ch));
            }
            else
            {
                $result = curl_exec($ch);
            }

            curl_close($ch);
        }

        return $result;
    }

    /**
     * Метод строит из int'ового кол-ва байт читаемое значение размера. Полезно для формирования списка файлов.
     * @param $size - (int) размер файла для конвертации.
     */
    public static function GetHumanSize($size = 0)
    {
        $mb = 1024 * 1024;
        $gb = $mb * 1024;
        $tb = $gb * 1024;
        $pb = $tb * 1024;

        if ($size < 1024)
        {
            return $size . ' Б';
        }
        elseif ($size >= 1024 && $size < $mb)
        {
            return round($size / 1024, 2) . ' КБ';
        }
        elseif ($size >= $mb && $size < $gb)
        {
            return round($size / $mb, 2) . ' МБ';
        }
        elseif ($size >= $gb && $size < $tb)
        {
            return round($size / $gb, 2) . ' ГБ';
        }
        elseif ($size >= $tb && $size < $pb)
        {
            return round($size / $tb, 2) . ' ТБ';
        }
        else
        {
            return 'Много!';
        }
    }

    /**
     * Аналог CApplication::IncludeFile из Битрикса, но без лишних "наворотов".
     * @note Третий параметр $arFunctionParams не используется, но объявлен для совместимости с оригинальным методом.
     * @SupressWarning('Unused')
     */
    public static function includeFile($rel_path, $arParams = array(), $arFunctionParams = array())
    {
        //		global $APPLICATION, $USER, $DB, $MESS, $DOCUMENT_ROOT;
        if (substr($rel_path, 0, 1) != "/")
        {
            $path = BX_PERSONAL_ROOT . "/templates/" . SITE_TEMPLATE_ID . "/" . $rel_path;
            if (!file_exists($_SERVER["DOCUMENT_ROOT"] . $path))
            {
                $path = BX_PERSONAL_ROOT . "/templates/.default/" . $rel_path;
                if (!file_exists($_SERVER["DOCUMENT_ROOT"] . $path))
                {
                    $path = BX_PERSONAL_ROOT . "/templates/" . SITE_TEMPLATE_ID . "/" . $rel_path;
                    $module_id = substr($rel_path, 0, strpos($rel_path, "/"));
                    if (strlen($module_id) > 0)
                    {
                        $path = "/bitrix/modules/" . $module_id . "/install/templates/" . $rel_path;
                        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . $path))
                        {
                            $path = BX_PERSONAL_ROOT . "/templates/" . SITE_TEMPLATE_ID . "/" . $rel_path;
                        }
                    }
                }
            }
        }
        else
        {
            $path = $rel_path;
        }

        if (is_file($_SERVER["DOCUMENT_ROOT"] . $path))
        {
            if (is_array($arParams))
            {
                extract($arParams, EXTR_SKIP);
            }

            include($_SERVER["DOCUMENT_ROOT"] . $path);
        }
        else
        {
            return;
        }
    }

    /**
     * Метод, генерирующий "стандартизированный" в компании AJAX-ответ от сервера.
     * @param bool $status - статус ответа.
     * @param mixed $answer - json-ответ. Если status = true, то должен быть ассоциативным массивом. если false - то либо массивом (массив ошибок, может быть ассоциативным), либо строкой (сгенерирует ключ errortext)
     * @return array ассоциативный массив ответа.
     */
    public static function setAjaxResult($status, $answer = '')
    {
        if ($status === true)
        {
            return is_array($answer) ? array_merge(array('status' => true), $answer) : array('status' => true);
        }
        elseif ($status === false)
        {
            return (is_array($answer)) ? array('status' => false, 'errors' => $answer) : array(
                'status' => false,
                'message' => $answer
            );
        }
        else
        {
            return array('status' => false, 'message' => 'Unknown error.');
        }
    }

    /**
     * Метод возвращает полное количество лет (полезная функция для подсчета возраста, количество полных лет)
     * @param $birthdayDate - входящая дата (дата с)
     * @return string|null
     */
    public static function getFullYears($birthdayDate)
    {
        if (!class_exists('\DateTime'))
        {
            return null;
        }

        $datetime = new \DateTime($birthdayDate);
        $interval = $datetime->diff(new \DateTime(date("Y-m-d")));

        return $interval->format("%Y");
    }

    /**
     * Определяет тип значения параметра и возравщает значение, приведенное к данному типу
     * @param $value - значение параметра
     * @return float|int|string
     */
    public static function checkParamValue($value)
    {
        $value_n = str_replace(',', '.', $value);

        if (is_numeric($value_n))
        {
            if (is_float($value_n + 0))
            {
                return floatval($value_n);
            }
            else
            {
                return intval($value_n);
            }
        }
        else
        {
            return htmlspecialchars($value);
        }
    }

    /**
     * Метод возвращает отформатированную в формат +7(000)000-00-00 строку с номером телефона
     * @param $phone - номер телефона (строка типа: 79991110022)
     * @return string
     */
    public static function formatPhone($phone)
    {
        if (empty($phone) || strlen($phone) <= 10)
        {
            return "";
        }
        else
        {
            if (strlen($phone) == 11)
            {
                $country = "";
                $area = "";
                $prefix = "";
                $exchange = "";
                $extension = "";

                sscanf($phone, "%1s%3s%3s%2s%2s", $country, $area, $prefix, $exchange, $extension);

                $out = "+";
                $out .= isset($country) ? $country . ' ' : '';
                $out .= isset($area) ? '(' . $area . ') ' : '';
                $out .= $prefix . '-' . $exchange;
                $out .= isset($extension) ? '-' . $extension : '';

                return $out;
            }
            else
            {
                return "";
            }
        }
    }

    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Method copies existing iblock, props and elements to other iblock type.
     * @warn Method does not copy iblock sections!
     * @param int $iblockId - source iblock id
     * @param string $iblockType - target iblock type
     * @param array $arSiteId - array of sites to bind the new iblock
     * @param bool $bCopyRights - whether to copy rights settings. Default true.
     * @param bool $bCopyProps - whether to copy iblock properties. Default true.
     * @param bool $bCopyElements - whether to copy iblock elements. Default false.
     * @return bool
     */
    public static function copyIblock($iblockId, $iblockType, $arSiteId, $bCopyRights = true, $bCopyProps = true, $bCopyElements = false)
    {
        $conn = Application::getConnection();

        try
        {
            Loader::includeModule('iblock');
        } catch (LoaderException $e)
        {
            return false;
        }

        $iblock = new CIblock;
        $property = new CIBlockProperty();
        $propEnumMapping = array(); // old id => new id

        try
        {
            $conn->startTransaction();
        } catch (SqlQueryException $e)
        {
            return false;
        }

        $success = true;

        $arIblock = $iblock->GetByID($iblockId)->Fetch();

        $arIblock['IBLOCK_TYPE_ID'] = $iblockType;
        $arIblock['SITE_ID'] = $arSiteId;

        $arPermissions = array();
        if ($arIblock['RIGHTS_MODE'] == 'E' && $bCopyRights)
        {
            $iblockRights = new CIBlockRights($iblockId);
            $arRights = $iblockRights->GetRights();
            foreach ($arRights as $k => $v)
            {
                $arIblock['RIGHTS']['n' . $k] = $v;
            }
        }
        elseif ($bCopyRights)
        {
            $arPermissions = $iblock->GetGroupPermissions($iblockId);
        }

        if (array_key_exists('API_CODE', $arIblock))
        {
            $arIblock['API_CODE'] = $arIblock['API_CODE'] . '_' . uniqid();
        }

        $newIblockId = $iblock->Add($arIblock);
        if (!$newIblockId)
        {
            $success = false;
        }

        if ($success && $arIblock['RIGHTS_MODE'] == 'S' && $bCopyRights)
        {
            CIBlock::SetPermission($newIblockId, $arPermissions);
        }

        if ($success && $bCopyProps)
        {
            $obProperties = CIBlockProperty::GetList(array(), array(
                'IBLOCK_ID' => $iblockId,
            ));

            while ($arProperty = $obProperties->Fetch())
            {
                $arProperty['IBLOCK_ID'] = $newIblockId;
                $propId = $property->Add($arProperty);
                if ($propId > 0)
                {
                    if ($arProperty['PROPERTY_TYPE'] == 'L')
                    {
                        $obEnum = CIBlockPropertyEnum::GetList(array(), array(
                            'IBLOCK_ID' => $iblockId,
                            'PROPERTY_ID' => $arProperty['ID'],
                        ));

                        while ($arEnum = $obEnum->Fetch())
                        {
                            $arEnum['PROPERTY_ID'] = $propId;
                            $newEnumId = CIBlockPropertyEnum::Add($arEnum);
                            $propEnumMapping[$arEnum['ID']] = $newEnumId;
                        }
                    }
                }
                else
                {
                    $success = false;
                }
            }

            unset($obEnum, $arEnum, $arProperty, $propId);
        }

        if ($success && $bCopyElements)
        {
            $elem = new CIBlockElement();
            $res = CIBlockElement::GetList(array(), array(
                'IBLOCK_ID' => $iblockId,
            ));

            while ($r = $res->GetNextElement())
            {
                $arFields = $r->GetFields();

                $arFields['IBLOCK_ID'] = $newIblockId;

                if ($arFields['PREVIEW_PICTURE'] > 0)
                {
                    $arFields['PREVIEW_PICTURE'] = CFile::MakeFileArray($arFields['PREVIEW_PICTURE']);
                }

                if ($arFields['DETAIL_PICTURE'] > 0)
                {
                    $arFields['DETAIL_PICTURE'] = CFile::MakeFileArray($arFields['DETAIL_PICTURE']);
                }

                $arProps = $r->GetProperties();
                $arFields['PROPERTY_VALUES'] = array();
                foreach ($arProps as $propCode => $arProperty)
                {
                    if ($arProperty['PROPERTY_TYPE'] == 'F')
                    {
                        if ($arProperty['MULTIPLE'] == 'Y')
                        {
                            if (is_array($arProperty['VALUE']) && count($arProperty['VALUE']) > 0)
                            {
                                foreach ($arProperty['VALUE'] as $key => $fileId)
                                {
                                    $arFields['PROPERTY_VALUES'][$propCode]['n' . $key] = CFile::MakeFileArray($fileId);
                                }
                            }
                        }
                        else
                        {
                            if ($arProperty['VALUE'] > 0)
                            {
                                $arFields['PROPERTY_VALUES'][$propCode] = CFile::MakeFileArray($arProperty['VALUE']);
                            }
                        }
                    }
                    elseif ($arProperty['PROPERTY_TYPE'] == 'L')
                    {
                        if ($arProperty['MULTIPLE'] == 'Y')
                        {
                            foreach ($arProperty['VALUE'] as $key => $value)
                            {
                                $arFields['PROPERTY_VALUES'][$propCode][] = $propEnumMapping[$value];
                            }
                        }
                        else
                        {
                            $arFields['PROPERTY_VALUES'][$propCode] = $propEnumMapping[$arProperty['VALUE_ENUM_ID']];
                        }
                    }
                    elseif ($arProperty['PROPERTY_TYPE'] == 'S' && $arProperty['USER_TYPE'] == 'HTML')
                    {
                        $arFields['PROPERTY_VALUES'][$propCode]['VALUE'] = $arProperty['VALUE'];
                    }
                    else
                    {
                        $arFields['PROPERTY_VALUES'][$propCode] = $arProperty['VALUE'];
                    }
                }
                unset($arFields['ID']);

                foreach ($arFields as $k => $v)
                {
                    if (stristr($k, '~') || is_null($v))
                    {
                        unset($arFields[$k]);
                    }
                }

                $eid = $elem->Add($arFields);
                if (!$eid)
                {
                    $success = false;
                }
            }
        }

        if (!$success)
        {
            try
            {
                $conn->rollbackTransaction();
            } catch (SqlQueryException $e)
            {
                return false;
            }
        }
        else
        {
            try
            {
                $conn->commitTransaction();
            } catch (SqlQueryException $e)
            {

            }

            //            if (COption::GetOptionString("iblock", "event_log_iblock", "N") === "Y")
            //            {
            //        global $USER;
            //        CEventLog::Log("IBLOCK", "IBLOCK_COPY", "iblock", $iblockId, serialize(array(
            //            "USER_ID" => $USER->GetID(),
            //            "NEW_IBLOCK_ID" => $newIblockId,
            //        )));
            //            }
        }

        return $success;
    }

    /**
     * @param $fileId
     * @return bool
     * array (
    'CONDITION' => '#^/file/([0-9]+)/.*#',
    'RULE' => 'ID=$1',
    'PATH' => '/file/download.php',
    'SORT' => 100,
    )
     */
    public static function downloadFile($fileId)
    {
        $fileId = intval($fileId);
        $rsFile = CFile::GetById($fileId);
        if ($arFile = $rsFile->Fetch())
        {
            if (preg_match('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
            {
                $UserBrowser = 'Opera';
            }
            elseif (stristr($_SERVER['HTTP_USER_AGENT'], 'YaBrowser'))
            {
                $UserBrowser = 'YandexBrowser';
            }
            else
            {
                if (preg_match('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']) || stristr($_SERVER['HTTP_USER_AGENT'], 'rv:11'))
                {
                    $UserBrowser = 'IE';
                }
                else
                {
                    $UserBrowser = '';
                }
            }

            header('Content-Type: ' . $arFile['CONTENT_TYPE']);
            $arExtension = explode('.', $arFile['FILE_NAME']);
            $filename = $arFile['ORIGINAL_NAME'];
            if (strlen($arFile["DESCRIPTION"]) > 0)
            {
                $filename = $arFile["DESCRIPTION"] . '.' . $arExtension[1];
                $bad = array_merge(array_map('chr', range(0, 31)), array("<", ">", ":", '"', "/", "\\", "|", "?", "*"));
                $filename = str_replace($bad, "", $filename);
            }

            if ($UserBrowser == 'IE')
            {
                $filename = iconv('UTF-8', 'cp1251', $filename);
            }

            if (ob_get_level())
            {
                ob_end_clean();
            }

            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Accept-Ranges: bytes');
            header('Cache-control: private');
            header('Pragma: private');
            if (isset($_SERVER['HTTP_RANGE']))
            {
                list($a, $Range) = explode('=', $_SERVER['HTTP_RANGE']);
                str_replace($Range, "-", $Range);
                $SizeOut = $arFile['FILE_SIZE'] - 1;
                $RangeLength = $arFile['FILE_SIZE'] - $Range;
                header('HTTP/1.1 206 Partial Content');
                header('Content-Length: ' . $RangeLength);
                header('Content-Range: bytes ' . ($Range * $SizeOut / $arFile['FILE_SIZE']));
            }
            else
            {
                $SizeOut = $arFile['FILE_SIZE'] - 1;
                header("Content-Length: " . $arFile['FILE_SIZE']);
            }

            if ($FileResponse = fopen($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $arFile['SUBDIR'] . '/' . $arFile['FILE_NAME'], 'r'))
            {
                if (isset($_SERVER['HTTP_RANGE']))
                {
                    fseek($FileResponse, $RangeLength);
                }
                $bytes_send = 0;
                $BufferSize = 1 * (1024 * 1024);
                while (!feof($FileResponse) and (connection_status() == 0))
                {
                    $BufferOut = fread($FileResponse, $BufferSize);
                    print($BufferOut);
                    flush();
                    $bytes_send += strlen($BufferOut);
                }
                fclose($FileResponse);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }

        return true;
    }
}