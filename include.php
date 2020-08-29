<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists('d'))
{
	function d($var, $var_dump = false)
	{
		Imaweb\Tools\Main::d($var, $var_dump);
	}
}

if (!function_exists('declOfNum'))
{
	function declOfNum($num, $titles)
	{
		return Imaweb\Tools\Main::declOfNum($num, $titles);
	}
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}