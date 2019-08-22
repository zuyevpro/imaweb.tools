<?php
namespace Imaweb\Tools;

use Bitrix\Main\Config\Option,
	\Bitrix\Main\Application;

interface IMigration
{
	public function up();
	public function down();
}

class Migration
{
	private static $_lastError = null;

	private static $migrationsDir = '/local/migrations/';

	/**
	 * @brief Метод создаёт новый файл с миграцией.
	 * @param string $name - название миграции, должно соответствовать регулярному выражению /[^A-Za-z0-9 _ .-]/
	 * @return bool|string - false в случае ошибки, строку с названием созданного файла в директории $migrationsDir. В случае false можно вызывать getLastError() и узнать текст возникшей ошибки.
	 */
	public static function add(string $name)
	{
		$docRoot = Application::getDocumentRoot();
		$doneDir = $docRoot . self::$migrationsDir . 'done/';
		$processDir = $docRoot . self::$migrationsDir . 'process/';
		CheckDirPath($doneDir);
		CheckDirPath($processDir);

		if (strlen($name) == 0)
		{
			self::$_lastError = "Не указано название";
			return false;
		}
		else
		{
			$name = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $name);
			if (strlen($name) == 0)
			{
				self::$_lastError = "Некорректное название миграции";
				return false;
			}
		}

		$fileName = date('YmdHis') . '_' . $name . '.php';
		$f = fopen($processDir . $fileName, 'w+');
		$tplPath = getLocalPath('modules/imaweb.tools/templates/migration.tpl');
		fwrite($f, sprintf(file_get_contents($docRoot . $tplPath), $name));
		fclose($f);

		return $fileName;
	}

	public static function getList()
	{
		$docRoot = Application::getDocumentRoot();
		$doneDir = $docRoot . self::$migrationsDir . 'done/';
		$processDir = $docRoot . self::$migrationsDir . 'process/';

		CheckDirPath($doneDir);
		CheckDirPath($processDir);

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
						'applied' => file_exists($doneDir . $arFile['filename']),
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
	public static function getLastError()
	{
		return self::$_lastError;
	}

	/**
	 * @brief Метод запуска неприменённых миграций или отката всех применённых.
	 * @param bool $migrate - true для миграции, false для откатывания
	 * @return bool
	 */
	public static function run($migrate = true)
	{
		$docRoot = Application::getDocumentRoot();
		$doneDir = $docRoot . self::$migrationsDir . 'done/';
		$processDir = $docRoot . self::$migrationsDir . 'process/';

		$logger = Logger::getInstance('migration');
		$logger->enabled(Option::get('main', 'update_devsrv') == 'Y');

		CheckDirPath($doneDir);
		CheckDirPath($processDir);

		$ar = glob($processDir . '*.php');

		foreach ($ar as $migrationPath)
		{
			@require($migrationPath);

			list($fileName) = explode('.', basename($migrationPath));

//			$id = substr($fileName, 0, 14);
			$className = '\\Imaweb\\Tools\\Migrations\\' . substr($fileName, 15);

			$methodName = $migrate ? 'up' : 'down';

			if (($migrate && !file_exists($doneDir . $fileName)) || (!$migrate && file_exists($doneDir . $fileName)))
			{
				if (class_exists($className))
				{
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
						catch (\Exception $e)
						{
							$logger->error($e->getMessage());
						}

						if ($success)
						{
							$conn->commitTransaction();
							if ($migrate)
							{
								$f = fopen($doneDir . $fileName, 'w+');
								fwrite($f, '1');
								fclose($f);
							}
							else
							{
								@unlink($doneDir . $fileName);
							}
						}
						else
						{
							$conn->rollbackTransaction();
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	public static function clear()
	{
		$docRoot = Application::getDocumentRoot();
		$doneDir = $docRoot . self::$migrationsDir . 'done/';
		$processDir = $docRoot . self::$migrationsDir . 'process/';

		CheckDirPath($doneDir);
		CheckDirPath($processDir);

		$ar = glob($doneDir . '*');

		foreach ($ar as $doneFile)
		{
			@unlink($processDir . basename($doneFile) . '.php');
		}

		return true;
	}
}