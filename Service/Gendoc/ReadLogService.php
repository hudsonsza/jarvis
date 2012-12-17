<?php
/**
 * LICENSE: This source file is subject to Creative Commons Attribution
 * 3.0 License that is available through the world-wide-web at the following URI:
 * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
 * and use this script commercially/non-commercially. My only requirement is that
 * you keep this header as an attribution to my work. Enjoy!
 *
 * @license http://creativecommons.org/licenses/by/3.0/
 *
 * @package Service
 * @subpackage Gendoc
 * @author Humberto Pereira <humberto@gendoc.com.br>
 *
 * @encoding UTF-8
 * @created 30.12.2011 20:29:55
 *
 * @filesource
 */

namespace Service\Gendoc;

define ('STAT_TIME_LAST_MODIFICATION', 9);
define ('STAT_FILE_SIZE', 7);

class ReadLogService extends \Library\IRC\Service\ServiceBase {


    private /* string */ $logFile;

    private $lastTime = null;
    
    private $lastSize = 0;

    private $filters;

    private $command;

    private $channels;


    public function __construct($logFile) {
        $this->logFile = escapeshellcmd ($logFile);

        $this->setCommand('Say');
    }

    public function __toString() {
        return __CLASS__;
    }

    public function start() {
        if (!file_exists($this->logFile))
            throw new Exception ('File ' . $this->logFile .' don\'t exists');

        $stat = stat($this->logFile);

        $this->lastTime = $stat[STAT_TIME_LAST_MODIFICATION];
        $this->lastSize = $stat[STAT_FILE_SIZE];

        $this->started = true;
    }

    public function stop() {
        $this->started = false;
    }

    /**
     * Le os ultimos n bytes do arquivo de log e retorna
     * 
     * @param integer $bytes - nro de bytes a serem lidos
     *
     * @return string contendo os ultimos n bytes do arquivo de log
     *
     * @throw Exception caso na consiga abrir o arquivo de log
     *
     */
    private function readLastBytes ($bytes) {

        if (!is_readable($this->logFile))
            throw new Exception($this->logFile . ' isn\'t readable');
            
        $fp = fopen ($this->logFile, 'r');

        /* falha ao abrir o arquivo */
        if (!$fp) {
            throw new Exception ('Error opening ' . $this->logFile);
        }

        fseek ($fp, $this->lastSize);

        $content = fread ($fp, $bytes);

        fclose ($fp);

        return $content;
    }

    public function consume () {
        clearstatcache();

        $stat = stat($this->logFile);    

        $newTime   = $stat[STAT_TIME_LAST_MODIFICATION];
        $newSize   = $stat[STAT_FILE_SIZE];

        if ($this->lastTime != $newTime && $this->lastSize < $newSize)
        {
            /* calcula a quantidade de bytes a serem lidos */
            $variation = $newSize - $this->lastSize;

            /* le os bytes */
            $content = $this->readLastBytes ($variation);

            /* divide em linhas */
            $rows = explode("\n", $content);

            foreach ($rows as $row)
            {
                if (!empty($this->filters))
                {
                    foreach ($this->filters as $filter) 
                    {                
                        $found = preg_match($filter, $row);

                        if ($found) {
                            $this->executeCommands ($row);
                            break;
                        }
                    }
                }
                else
                    $this->executeCommands ($row);
            }
        }

        $this->lastTime = $newTime;
        $this->lastSize = $newSize;
    }

    private function executeCommands($line) 
    {
        if (empty ($line))
            return;

        foreach ($this->channels as $channel) 
        {            
            $this->bot->executeCommand($this->bot->getNick(), $this->command, array ($channel, $line));
        }
    }

    public function setFilters($filters) {
        $this->filters = $filters;
    }

    public function setChannels($channels) {
        $this->channels = $channels;
    }

    public function setCommand($command) {
        $this->command = $command;
    }
}
?>
