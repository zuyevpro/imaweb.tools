<?php
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Imaweb\Tools\MigrationEngine;
use Imaweb\Tools\Logger;

if (php_sapi_name() === 'cli') {
	$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../../..';
}

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

try {
    Loader::includeModule('imaweb.tools');
}
catch (LoaderException $e) {
    die(1);
}

$logger = Logger::getInstance('migration', Logger::WARNING, Logger::DIRECTION_STDOUT);
if (!MigrationEngine::getInstance()->run(true)) {
    $logger->warn('Migration run failed', [
        'message' => MigrationEngine::getInstance()->getLastError(),
    ]);
}
else {
    $logger->info('Migration run success');
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');