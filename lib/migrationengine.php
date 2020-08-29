<?php
namespace Imaweb\Tools;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use \Exception;

interface IMigration
{
	public function up();
	public function down();
}

class MigrationEngine
{
    const MIGRATIONS_DIR = '/local/migrations/';

    private static $_instance;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @var string
     */
    private $lastError = null;

    /**
     * @var Logger
     */
    private $logger;

    private function getLogger() {
        if (is_null($this->logger)) {
            $level = Logger::WARNING;
            try {
                Option::get('main', 'update_devsrv') == 'Y' ? Logger::DEBUG : Logger::WARNING;
            }
            catch (ArgumentNullException $e) {

            }
            catch (ArgumentOutOfRangeException $e) {

            }

            $this->logger = Logger::getInstance(
                'migration',
                $level,
                Logger::DIRECTION_FILE
            );
        }

        return $this->logger;
    }

    /**
     * @param mixed $message
     */
	private function setLastError($message) {
	    if (!is_string($message)) {
	        $message = $message->__toString();
        }

	    $this->lastError = $message;
    }

	/**
	 * @brief Метод создаёт новый файл с миграцией.
	 * @param string $name - название миграции, должно соответствовать регулярному выражению /[^A-Za-z0-9 _ .-]/
	 * @return bool|string - false в случае ошибки, строку с названием созданного файла в директории $migrationsDir. В случае false можно вызывать getLastError() и узнать текст возникшей ошибки.
	 */
	public function add(string $name)
	{
		$docRoot = Application::getDocumentRoot();
		$processDir = $docRoot . self::MIGRATIONS_DIR . 'process/';
		CheckDirPath($processDir);

		if (strlen($name) == 0)
		{
			$this->setLastError("Не указано название");
			$this->getLogger()->error($this->lastError, [
			    'name' => $name,
            ]);
			return false;
		}
		else
		{
			$name = preg_replace('/[^A-Za-z0-9 _.-]/', '', $name);
			if (strlen($name) == 0)
			{
			    $this->setLastError("Некорректное название миграции");
                $this->getLogger()->error($this->lastError, [
                    'name' => $name,
                ]);
				return false;
			}
		}

		$fileName = date('YmdHis') . '_' . $name . '.php';
		$f = fopen($processDir . $fileName, 'w+');
		if (!$f) {
		    $this->setLastError('Ошибка создания миграции: нет доступа к файловой системе');
            $this->getLogger()->error($this->lastError, [
                'name' => $name,
            ]);
            return false;
        }

		$tplPath = getLocalPath('modules/imaweb.tools/templates/migration.tpl');
		fwrite($f, sprintf(file_get_contents($docRoot . $tplPath), $name));
		fclose($f);

		return $fileName;
	}

	public function getAppliedMigrations() {
        $migrations = [];
        try {
            $obApplied = MigrationTable::getList([
                'select' => [
                    'ID',
                    'NAME',
                ],
            ]);
            while ($arMigration = $obApplied->fetch()) {
                $migrations[$arMigration['ID']] = $arMigration['NAME'];
            }
        }
        catch (SystemException $e) {

        }

        return $migrations;
    }

	public function getList()
	{
		$docRoot = Application::getDocumentRoot();
		$processDir = $docRoot . self::MIGRATIONS_DIR . 'process/';

		CheckDirPath($processDir);

		$migrations = $this->getAppliedMigrations();

		$arFiles = glob($processDir.  '*');

		$arResult = array();
		foreach ($arFiles as $migrationFile)
		{
			$arFile = pathinfo($migrationFile);
			if ($arFile['extension'] == 'php')
			{
				$matches = array();
				preg_match('/^([0-9]+)_(.*)/', $arFile['filename'], $matches);

				$dateTime = $matches[1];
				$className = $matches[2];

				if (strlen($dateTime) > 0 && strlen($className) > 0)
				{
					$arItem = array(
						'date' => $dateTime,
						'name' => $className,
						'applied' => in_array($arFile['filename'], $migrations),
					);

					$className = '\\Imaweb\\Tools\\Migrations\\' . $className;

					if (!class_exists($className))
					{
						require($processDir . $arFile['basename']);
					}

					if (class_exists($className))
					{
						if (method_exists($className, 'getName'))
						{
							$prettyName = $className::getName();
							if (strlen($prettyName) > 0)
							{
								$arItem['name'] = $prettyName . " ($className)";
							}
						}
					}

					$arResult[] = $arItem;
				}
			}
		}

		return $arResult;
	}

	/**
	 * @return null|string
	 */
	public function getLastError()
	{
		return $this->lastError;
	}

	/**
	 * @brief Метод запуска неприменённых миграций или отката всех применённых.
	 * @param bool $migrate - true для применения миграции, false для её отката
	 * @return bool
	 */
	public function run($migrate = true)
	{
		$docRoot = Application::getDocumentRoot();
		$processDir = $docRoot . self::MIGRATIONS_DIR . 'process/';

		CheckDirPath($processDir);

		$ar = glob($processDir . '*.php');

		$arApplied = $this->getAppliedMigrations();

		foreach ($ar as $migrationPath)
		{
			@require($migrationPath);

			list($fileName) = explode('.', basename($migrationPath));

			$className = '\\Imaweb\\Tools\\Migrations\\' . substr($fileName, 15);

			$methodName = $migrate ? 'up' : 'down';

			$applied = in_array($fileName, $arApplied);

			if (($migrate && !$applied) || (!$migrate && $applied))
			{
				if (class_exists($className))
				{
				    try {
                        $conn = Application::getConnection();
                        $instance = new $className();
                        if (method_exists($instance, $methodName))
                        {
                            $conn->startTransaction();
                            $success = false;
                            try {
                                $instance->$methodName();
                                $success = true;
                            }
                            catch (Exception $e)
                            {
                                list(, $name) = explode('_', $fileName);
                                $this->setLastError($name . ': ' . $e->getMessage());
                                $this->getLogger()->error('Migration exception', [
                                    'exception' => $e,
                                ]);
                            }

                            if ($success)
                            {
                                $conn->commitTransaction();
                                if ($migrate)
                                {
                                    MigrationTable::add([
                                        'NAME' => $fileName,
                                    ]);
                                }
                                else
                                {
                                    MigrationTable::delete(array_search($fileName, $arApplied));
                                }
                            }
                            else
                            {
                                $conn->rollbackTransaction();
                                return false;
                            }
                        }
                    }
                    catch (Exception $e) {
				        $this->getLogger()->error('Caught exception', [
				            'exception' => $e,
                        ]);
                    }
				}
			}
		}

		return true;
	}

	public function clear()
	{
		$docRoot = Application::getDocumentRoot();
		$processDir = $docRoot . self::MIGRATIONS_DIR . 'process/';

		CheckDirPath($processDir);

		$migrations = $this->getAppliedMigrations();

		foreach ($migrations as $doneFile)
		{
			@unlink($processDir . basename($doneFile) . '.php');
		}

		return true;
	}
}