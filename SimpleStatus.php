<?php
/**
 * Singleton class for status page fragments and display functions
 *
 * @author John Day jdayworkplace@gmail.com
 */

class SimpleStatus
{
    private array $cfg = [];

    public function __construct() {
        $this->cfg = require 'configuration.php';
    }

    /**
     * Select output to generate based on page requested
     *
     * @param $page
     * @return string
     */
    public function PageContents($page):string{
        switch($page){
            case 'phpinfo':
                return $this->PhpInfoPage();
            case 'notes':
                return $this->NotesPage();
            default:
                //fall through to status page
        }
        return $this->StatusPage();
    }

    /**
     * Returns standard phpinfo result
     *
     * @return string
     */
    private function PhpInfoPage():string{
        ob_start();
        echo phpinfo();
        $result = ob_get_contents();
        ob_clean();
        return $result;
    }

    /**
     * Returns serverNotes result
     *
     * @return string
     */
    private function NotesPage():string{
        ob_start();
        require_once 'serverNotes.html.php';
        $result = ob_get_contents();
        ob_clean();

        return $result;
    }

    /**
     * Return status page
     * using the url(s) defined in the configuration file
     * this appends the query params 'full&json'
     *
     * @return string
     */
    private function StatusPage():string{
        $result = $this->ServerDetails();

        foreach($this->cfg['php-versions'] as $phpVersion => $statusUrl){
            $versionStatusHTML = $this->retrieveFPMStatus($statusUrl.'?full&json');
            $result .= "<br>PHP $phpVersion<br>" .  $versionStatusHTML;
        }

        return $result;
    }

    /**
     * Collects and creates a table with an overview of the Server status
     *
     * Collects all the required details and then displays them via a template capture
     * serverDetails.html.php
     *
     * @return string
     */
    private function ServerDetails():string{
        $load = sys_getloadavg();
        $cpuload = round($load[0],2);
        $cpu_count = shell_exec('nproc');
        $free = shell_exec('free -b');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);

        $mem = $this->cleanUpArray($free_arr[1]);
        $memtotal = $this->convertBytesToHuman($mem[1],3);
        $memused = $this->convertBytesToHuman($mem[2],3);
        $memfree = $this->convertBytesToHuman($mem[3],3);
        $memshared = $this->convertBytesToHuman($mem[4],3);
        $memcached = $this->convertBytesToHuman($mem[5],3);
        $memavailable = $this->convertBytesToHuman($mem[6],3);

        // Linux Connections
        $connections = `netstat -ntu | grep -E ':80 |443 ' | grep ESTABLISHED | grep -v LISTEN | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | grep -v 127.0.0.1 | wc -l`;
        $totalconnections = `netstat -ntu | grep -E ':80 |443 ' | grep -v LISTEN | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -rn | grep -v 127.0.0.1 | wc -l`;
        // used / total
        $memusage = round( (round($mem[2] / 1000000,2) / round($mem[1] / 1000000,2)) * 100 );

        // swap
        $mem = $this->cleanUpArray($free_arr[2]);
        $swaptotal = $this->convertBytesToHuman($mem[1],3);
        $swapused = $this->convertBytesToHuman($mem[2],3);
        $swapfree = $this->convertBytesToHuman($mem[3],3);

        $phpload = round(memory_get_usage() / 1000000,2);

        $diskfree = round(disk_free_space(".") / 1000000000);
        $disktotal = round(disk_total_space(".") / 1000000000);
        $diskused = round($disktotal - $diskfree);

        $diskusage = round($diskused/$disktotal*100);

        ob_start();
        require_once 'serverDetails.html.php';
        $result = ob_get_contents();
        ob_clean();

        return $result;
    }

    /**
     * Attempts to decode data as json and creates a html table for display
     *
     * @param string $data
     * @return string
     * @throws Exception
     */
    private function convertFPMStatusResultToHTML(string $data){
        if (!$data) return "Failed to retrieve data";
        $json = json_decode($data,true);
        if (!$json) return "Failed to retrieve data";
        $result = "";
        $subheader = false;

        $result .= "<table class='fpmtable' style='width:100%;padding-bottom:20px;'>";
        $cells = [];
        $cellsPerRow = 4;
        foreach($json as $k=>$v){
            if ($k != 'processes'){
                switch($k){
                    case 'start time':{
                        $cells[]= "<b>$k</b> : ".$this->convertToDate($v)."<br>";
                        break;
                    }
                    case 'start since':{
                        $cells[]= "<b>$k</b> : ".$this->convertSecondsToHuman($v)."<br>";
                        break;
                    }
                    default:{
                        $cells[]= "<b>$k</b> : ".$v." <br>";
                    }
                }
                if (count($cells) == $cellsPerRow){
                    $result .= "<tr><td>".implode("</td><td>",$cells)."</td></tr>";
                    $cells = [];
                }
            }
        }
        // handle semi complet row
        if (count($cells) > 0){
            while(count($cells) < $cellsPerRow) $cells[]="";
            $result .= "<tr><td>".implode("</td><td>",$cells)."</td></tr>";
        }
        $result.="</table>";

        // processes table
        $result .= "<table border=1>";
        foreach($json['processes'] as $p){
            if (!$subheader){
                $subresult = implode("</th><th>",array_keys($p))."<br>";
                $result .= "<tr><th>".$subresult."</th></tr>";
                $subheader = true;
            }

            $s = [];
            foreach($p as $pk=>$pv){
                switch($pk){
                    case 'last request memory':{
                        $s[] = $this->convertBytesToHuman($pv);
                        break;
                    }
                    case 'start time':{
                        $s[] = $this->convertToDate($pv);
                        break;
                    }
                    case 'start since':
                    case 'request duration':{
                        $s[] = $this->convertSecondsToHuman($pv);
                        break;
                    }
                    default:{
                        $s[] = htmlentities($pv, ENT_QUOTES);
                    }
                }
            }
            $subresult = implode("</td><td>",array_values($s));
            $result .= "<tr><td>".$subresult."</td></tr>";
        }
        $result .= "</table>";

        return $result;
    }

    /**
     *  Use curl to collect fpm-status
     * @param $url
     * @return string
     */
    private function retrieveFPMStatus($url):string{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $this->convertFPMStatusResultToHTML($data);
    }


    /**
     * Helper function to process return string from cli commands
     *
     * @param $output
     * @return false|string[]
     */
    private function cleanUpArray($output){
        $mem = explode(" ",$output);
        $mem = array_filter($mem, function($value) { return ($value !== null && $value !== false && $value !== ''); }); // removes nulls from array
        return array_merge($mem);
    }


    /**
     * Convert bytes to human-readable string
     * (can't claim the credit here, found and tweaked it)
     * @param int $size
     * @param false|int $scale (non-false = request scale in a particular unit)
     * @return string
     */
    private function convertBytesToHuman(int $size, $scale = false):string{
        $units = array('B','KB','MB','GB','TB','PB');
        $size += 0;
        if ($size < 1) return $size;
        if (!$scale){
            $scale = floor(log($size,1024));
        }
        $denom = pow(1024,$scale);
        $r = $size / $denom;
        return round($r,2).$units[$scale];
    }

    /**
     * Convert seconds to human-readable string
     * @param int $seconds
     * @return string
     */
    private function convertSecondsToHuman(int $seconds):string{
        if (!$seconds) return '-';
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format($this->cfg['timesince-format']);

    }

    /**
     * DateTime conversion for display
     *
     * @param $timestamp
     * @return string
     * @throws Exception
     */
    private function convertToDate($timestamp):string{
        $dt = new DateTime('@'.$timestamp);
        $dt->setTimezone(new DateTimeZone($this->cfg['timezone']));
        return $dt->format($this->cfg['datetime-format']);
    }

    /**
     * generate a navigation bar with links based on pages/sections available
     *
     * @return string
     */
    public function NavigationBar($current_page){
        $result = '';

        foreach($this->cfg['pages'] as $page){
            $classes = 'navlink';
            if ($current_page == $page){
                $classes .= ' active';
            }
            $link = "<a href='".$this->cfg['path']."?page=$page' class='".$classes."'>".ucfirst($page)."</a>";
            $result .= $link;
        }

        return '<div id="navbar">'.$result.'</div>';
    }
};

