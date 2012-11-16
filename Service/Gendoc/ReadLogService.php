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

    public function start() {
        if (!file_exists($this->logFile))
            throw new Exception ('File ' . $this->logFile .' don\'t exists');

        $stat = stat($this->logFile);

        $stat = stat($this->logFile)
        $this->lastTime = $stat[STAT_TIME_LAST_MODIFICATION];
        $this->lastSize = $stat[STAT_FILE_SIZE];

        $this->started = true;
    }

    public function stop() {
        $this->started = false;
    }

    public function consume () {
        clearstatcache();

        $stat = stat($this->logFile);    

        $newTime   = $stat[STAT_TIME_LAST_MODIFICATION];
        $newSize   = $stat[STAT_FILE_SIZE];

        if ($this->lastTime != $newTime && $this->lastSize < $newSize)
        {
            $variation = $newSize - $this->lastSize;

            $command   = 'tail -c  ' . $variation . ' "' . $this->readFile . '"';

            $shell = shell_exec($command);
            $rows  = explode("\n", $shell);

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

        foreach($this->channels as $channel) 
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
