<?php

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$path = getLocalPath('modules/imaweb.tools/admin/' . basename(__FILE__));

if ($path)
{
	require($_SERVER['DOCUMENT_ROOT'] . $path);
}