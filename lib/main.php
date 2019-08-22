<?php
namespace Imaweb\Tools;

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
		$cases = array (2, 0, 1, 1, 1, 2);
		return $number . " " . $titles[($number%100 > 4 && $number %100 < 20) ? 2 : $cases[min($number%10, 5)]];
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
		if($var_dump)
			var_dump($ar);
		else
			print_r($ar);
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
		fwrite($f,print_r($var, true));
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
		$len = (mb_strlen($string) > $maxlen)
			? mb_strripos(mb_substr($string, 0, $maxlen), ' ')
			: $maxlen
		;
		$cutStr = mb_substr($string, 0, $len);
		return (mb_strlen($string) > $maxlen)
			? $cutStr . '...'
			: $cutStr
			;
	}

	/**
	 * Обертка для CURL-запроса по произовльному URL-адресу.
	 * @param $url - URL-адрес;
	 * @param $is_cp1251 - флаг, указывающий, отправлять ли запрос в кодировке 1251. Необязательный параметр. Значение по умолчанию: false.
	 * @note По умолчанию запросы отправляются в кодировке utf-8.
	 */
	public static function GetContents($url = '', $is_cp1251 = false)
	{
		if(strlen($url) == 0)
			$result = false;
		else
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 Safari/537.11'); //google chrome at 08.01.2013

			if($is_cp1251)
				$result = iconv('cp1251', 'utf8', curl_exec($ch));
			else
				$result = curl_exec($ch);

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

		if($size < 1024)
		{
			return $size . ' Б';
		}
		elseif($size >= 1024 && $size < $mb)
		{
			return round($size/1024, 2) . ' КБ';
		}
		elseif($size >= $mb && $size < $gb)
		{
			return round($size/$mb, 2) . ' МБ';
		}
		elseif($size >= $gb && $size < $tb)
		{
			return round($size/$gb, 2) . ' ГБ';
		}
		elseif($size >= $tb && $size < $pb)
		{
			return round($size/$tb, 2) . ' ТБ';
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
		if(substr($rel_path, 0, 1)!="/")
		{
			$path = BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/".$rel_path;
			if(!file_exists($_SERVER["DOCUMENT_ROOT"].$path))
			{
				$path = BX_PERSONAL_ROOT."/templates/.default/".$rel_path;
				if(!file_exists($_SERVER["DOCUMENT_ROOT"].$path))
				{
					$path = BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/".$rel_path;
					$module_id = substr($rel_path, 0, strpos($rel_path, "/"));
					if(strlen($module_id)>0)
					{
						$path = "/bitrix/modules/".$module_id."/install/templates/".$rel_path;
						if(!file_exists($_SERVER["DOCUMENT_ROOT"].$path))
						{
							$path = BX_PERSONAL_ROOT."/templates/".SITE_TEMPLATE_ID."/".$rel_path;
						}
					}
				}
			}
		}
		else
		{
			$path = $rel_path;
		}

		if(is_file($_SERVER["DOCUMENT_ROOT"].$path))
		{
			if(is_array($arParams))
				extract($arParams, EXTR_SKIP);

			include($_SERVER["DOCUMENT_ROOT"].$path);
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
		if($status === true)
		{
			return is_array($answer) ? array_merge(array('status' => true), $answer) : array('status' => true);
		}
		elseif($status === false)
		{
			return (is_array($answer)) ? array('status' => false, 'errors' => $answer) : array('status' => false, 'message' => $answer);
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
		$value_n = str_replace(',', '.',$value);

		if (is_numeric($value_n))
		{
			if(is_float($value_n + 0))
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
		else if(strlen($phone) == 11)
		{
			$country = "";
			$area = "";
			$prefix = "";
			$exchange = "";
			$extension = "";

			sscanf($phone, "%1s%3s%3s%2s%2s", $country, $area, $prefix, $exchange, $extension);

			$out = "+";
			$out .= isset($country) ? $country.' ' : '';
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

	public static function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}

	public static function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}
}