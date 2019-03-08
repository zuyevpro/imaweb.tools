<?
namespace Imaweb\Tools;

class Logger
{
    private $baseDir = null; /// Здесь хранится базовая директория хранения файла с логами.
    private $file = null; /// Указатель на файл.
    private $start = 0; /// Время создания экземпляра класса в мкс..
    private $_stdout = false;
    private $_html = false;

    private $app;

    private $_enabled = true;

    function __construct($logName = "")
    {
        if ($logName == "")
        {
            $logName = date('d') . '.log';
        }

        if (!stristr($logName, '.log'))
        {
            $logName .= '.log';
        }

        $this->start = microtime(true);

        $this->baseDir = $_SERVER['DOCUMENT_ROOT'] . '/logs/' . date('Y/m/');

        if(!is_dir($this->baseDir))
        {
            $res = mkdir($this->baseDir, 0755, true);
        }

        $this->file = fopen($this->baseDir . $logName, 'a+');

        global $APPLICATION;
        $this->app = &$APPLICATION;

    }

    function __destruct()
    {
        fclose($this->file);
    }

    /**
     * @brief Основной метод записи в файл. Формат сохраняемой строки лога определяется здесь.
     * @return boolean.
     */
    private function _log($level = "log", $msg = "")
    {
        if(!$this->file)
        {
            return false;
        }

        if ($this->_enabled)
        {
            $latency = round(microtime(true) - $this->start, 3);

            $line = date("[d.m.Y H:i:s]") . ' [' . sprintf("%.3f", $latency) . '] (' . $level . ') | ' . $msg . "\n";

            if ($this->_stdout)
            {
                echo $line . ($this->_html ? '<br/>' : '');
            }
            else
            {
                fwrite($this->file, $line);
            }
        }

        return true;
    }

    public static function getInstance($fileName = null)
    {
        if (is_null($fileName))
        {
            $fileName = date('d') . '.log';
        }

        return new static($fileName);
    }

    /**
     * @brief Добавляет в лог запись с маркером "info".
     * @return boolean.
     */
    public function info($msg)
    {
        return $this->_log('infor', $msg);
    }

    /**
     * @brief Добавляет в лог запись с маркером "log".
     * @return boolean.
     */
    public function log($msg)
    {
        return $this->_log('_log_', $msg);
    }

    /**
     * @brief Добавляет в лог запись с маркером "warn".
     * @note Рекомендуется использовать для записи предупреждений и некритичных ошибок.
     * @return boolean.
     */
    public function warn($msg)
    {
        return $this->_log('warni', $msg);
    }

    /**
     * @brief Добавляет в лог запись с маркером "error".
     * @note Рекомендуется использовать только для записи критических ошибок.
     * @return boolean.
     */
    public function error($msg)
    {


        //return $this->_log('error', $msg);
        $this->_log('error', $msg);
        $ex = $this->app->GetException();

        if ($ex instanceof \CApplicationException)
        {
            $this->_log('throw', 'Исключение: ' . $ex->GetString());
        }
    }

    /**
     * @brief Возвращает время с момента создания экземпляра класса.
     * @return mixed
     */
    public function getLatency()
    {
        return round(microtime(true) - $this->start, 3);
    }

    public function getMemoryPeakUsage()
    {
        return memory_get_peak_usage(true);
    }

    public function file()
    {
        return $this->file;
    }

    public function stdout($stage)
    {
        $stage = $stage === true;

        $this->_stdout = $stage;

        return $this;
    }

    public function html($stage)
    {
        $stage = $stage === true;
        $this->_html = $stage;

        return $this;
    }

    public function dump($variable, $sign = 'variable')
    {
        if ($this->_html)
        {
            $this->info('$' . $sign . " => <pre>" . print_r($variable, true) . '</pre>');
        }
        else
        {
            $this->info('$' . $sign . " => " . print_r($variable, true));
        }
    }

    public function enabled($flag = null)
    {
        if (is_null($flag))
        {
            return $this->_enabled;
        }

        $this->_enabled = $flag === true;
    }

	public function cleanOldData($baseDir = null, $numDays = 30)
	{
		if (is_null($baseDir))
		{
			$baseDir = $this->_baseDir . '*/*/*/';
		}

		$arFiles = glob($baseDir);

		$totalCleaned = 0;

		foreach ($arFiles as $filePath)
		{
			$timestamp = filemtime($filePath);

			if ($timestamp < time() - $numDays*86400)
			{
				$totalCleaned += filesize($filePath);
				//				echo $filePath . ' :: ' . date('d.m.Y H:i:s', $timestamp) . ' :: ' . filesize($filePath) . '<br/>';
				if (is_file($filePath))
				{
					unlink($filePath);
				}
			}
		}
	}
}