<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists('d'))
{
	function d($var, $var_dump = false)
	{
		return Imaweb\Tools\Main::d($var, $var_dump);
	}
}

if (!function_exists('x'))
{
	function x($var, $mode = 'w+', $file = 'x.txt')
	{
		return Imaweb\Tools\Main::x($var, $mode, $file);
	}
}

if (!function_exists('declOfNum'))
{
	function declOfNum($num, $titles)
	{
		return Imaweb\Tools\Main::declOfNum($num, $titles);
	}
}

if (!function_exists('cutString'))
{
	function cutString($str, $maxLen)
	{
		return Imaweb\Tools\Main::cutString($str, $maxLen);
	}
}

if (!function_exists('getFullYears'))
{
	function getFullYears($birthDate)
	{
		return Imaweb\Tools\Main::getFullYears($birthDate);
	}
}

if (!function_exists('formatPhone'))
{
	function formatPhone($phone)
	{
		return Imaweb\Tools\Main::formatPhone($phone);
	}
}