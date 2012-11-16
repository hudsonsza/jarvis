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

class ReadLogService extends \Library\IRC\Service\ServiceBase {

    private /* string */ $logFile;

    private $lastTime = null;
    
    private $lastSize = 0;

    private $filters;

    private $command;

    private $channels;


    public function __construct($logFile) {
        $this->logFile = $logFile;

        $this->setCommand('Say');
    }

    public function start() {
        if (!file_exists($this->logFile))
            thrown new Exception ('File ' . $this->logFile .' don\'t exists');

        $stat = stat($this->logFile)
        $this->lastTime = $stat[9];
        $this->lastSize = $stat[7];

        $this->started = true;
    }

    public function stop() {
        $this->started = false;
    }

    public function consume () {
        clearstatcache();

        $stat = stat($this->logFile);    

        $newTime   = $stat[9];
        $newSize   = $stat[7];

        if ($this->lastStatus[9] != $newTime[9])
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