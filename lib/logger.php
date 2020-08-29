<?
namespace Imaweb\Tools;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use \Exception;

class Logger
{
    private $baseDir = null; /// Здесь хранится базовая директория хранения файла с логами.
    private $file = null; /// Указатель на файл.
    private $start = 0; /// Время создания экземпляра класса в мкс..

    private $ip = null;

    private $minLevel = 100;

    const DIRECTION_FILE = 1;
    const DIRECTION_STDOUT = 2;
    const DIRECTION_RESPONSE = 3;

    private $app;

    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;

    function __construct($channel, $minLevel = self::WARNING, $direction = self::DIRECTION_FILE) {

        try {
            $this->ip = Application::getInstance()->getContext()->getRequest()->getRemoteAddress();
        }
        catch (SystemException $e) {

        }

        $this->start = microtime(true);

        $this->baseDir = $_SERVER['DOCUMENT_ROOT'] . '/logs/' . date('Y/m/d/');

        if (!is_dir($this->baseDir)) {
            mkdir($this->baseDir, 0755, true);
        }

        if ($direction == self::DIRECTION_FILE) {
            $this->file = fopen($this->baseDir . $channel . '.log', 'a+');
        }
        elseif ($direction == self::DIRECTION_STDOUT) {
            $this->file = fopen('php://stdout', 'w');
        }
        else {
            $this->file = null;
        }

        $this->minLevel = $minLevel;

        global $APPLICATION;
        $this->app = &$APPLICATION;

    }

    function __destruct() {
        if (!is_null($this->file)) {
            fclose($this->file);
        }
    }

    /**
     * @brief Основной метод записи в файл. Формат сохраняемой строки лога определяется здесь.
     * @return boolean.
     */
    private function _log($level, $msg = "", $ctx = null) {
        if ($level >= $this->minLevel) {
            $latency = round(microtime(true) - $this->start, 3);

            if (array_key_exists('exception', $ctx)) {
                if ($ctx['exception'] instanceof Exception) {
                    $ctx['exception'] = [
                        'message' => $ctx['exception']->getMessage(),
                        'file' => $ctx['exception']->getFile(),
                        'line' => $ctx['exception']->getLine(),
                    ];
                }
            }

            $line = date("[d.m.Y H:i:s]")
                . ' [' . sprintf("%.3f", $latency) . '] (' . $level . ') | '
                . $msg . ' '
                . json_encode($ctx, JSON_UNESCAPED_UNICODE) . ' '
                . json_encode(array(
                    'memory_usage' => $this->getMemoryPeakUsage(),
                    'cli' => php_sapi_name() === 'cli',
                    'ip' => $this->ip,
                ), JSON_UNESCAPED_UNICODE)
                . "\n";

            if (!is_null($this->file)) {
                fwrite($this->file, $line);
            }
            else {
                echo $line . '<br/>';
            }
        }

        return true;
    }

    public static function getInstance($channel, $minLevel = self::WARNING, $direction = self::DIRECTION_FILE) {
        return new static($channel, $minLevel, $direction);
    }

    public function debug($msg, $ctx = null) {
        return $this->_log(self::DEBUG, $msg, $ctx);
    }

    public function info($msg, $ctx = null) {
        return $this->_log(self::INFO, $msg, $ctx);
    }

    public function notice($msg, $ctx = null) {
        return $this->_log(self::NOTICE, $msg, $ctx);
    }

    public function warn($msg, $ctx = null) {
        return $this->_log(self::WARNING, $msg, $ctx);
    }

    public function warning($msg, $ctx = null) {
        return $this->_log(self::WARNING, $msg, $ctx);
    }

    public function error($msg, $ctx = null) {
        return $this->_log(self::ERROR, $msg, $ctx);
    }

    public function err($msg, $ctx = null) {
        return $this->_log(self::ERROR, $msg, $ctx);
    }

    public function critical($msg, $ctx = null) {
        return $this->_log(self::CRITICAL, $msg, $ctx);
    }

    public function crit($msg, $ctx = null) {
        return $this->_log(self::CRITICAL, $msg, $ctx);
    }

    public function alert($msg, $ctx = null) {
        return $this->_log(self::ALERT, $msg, $ctx);
    }

    public function emergency($msg, $ctx = null) {
        return $this->_log(self::EMERGENCY, $msg, $ctx);
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

    /**
     * @deprecated
     * @param $variable
     * @param string $sign
     */
    public function dump($variable, $sign = 'variable') {
        $this->info('dump', [
            $sign => $variable
        ]);
    }

	public function cleanOldData($baseDir = null, $numDays = 30) {
		if (is_null($baseDir)) {
			$baseDir = $this->baseDir . '*/*/*/';
		}

		$arFiles = glob($baseDir);

		$totalCleaned = 0;

		foreach ($arFiles as $filePath) {
			$timestamp = filemtime($filePath);

			if ($timestamp < time() - $numDays*86400) {
				$totalCleaned += filesize($filePath);
				if (is_file($filePath)) {
					unlink($filePath);
				}
			}
		}
	}

    /**
     * @param null $flag
     * @deprecated
     * @return bool
     */
    public function enabled($flag = null)
    {
        return true;
    }
}
