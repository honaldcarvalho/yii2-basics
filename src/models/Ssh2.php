<?php

namespace weebz\yii2basics\modules\common\models;

use Exception;
use Yii;
use yii\base\Model;
use phpseclib3\Net\SSH2 as NetSSH2;

/**
 * This is the model class for table "cities".
 *
 * @property string $host
 * @property string $port
 * @property string $username
 * @property string $password
 *
 * @property State $state
 */
class Ssh2 extends ModelCommon
{
    public $conn = null;

    function __construct($host = null, $username = null, $password = null,$port = 22)
    {
        try{
            $ssh = new NetSSH2($host,$port);
            $ssh->setWindowSize(1920, 1920);
            $ssh->setTimeout(3);
            if ($ssh->isConnected() === false || $ssh->isAuthenticated() === false) {
                if(!$ssh->login($username, $password)){
                    echo "Erro ao autenticar \n";
                    $this->conn = null;
                }
            }else{
                $ssh->setTimeout(1);
            }
            $this->conn = $ssh;
        }catch(Exception $e){
            $this->conn = null;
        }
        
    }

    function setTimeout($time)
    {
        if (isset($time) && isset($this->conn)) {
            try {
                $this->conn->setTimeout($time);
                return true;
            } catch (\Throwable $th) {
                $this->conn->disconnect();
                return false;
            }
        } else {
            false;
        }
    }


    function runShell($command = null)
    {
        $out = '';
        if (isset($command) && isset($this->conn)) {
            sleep(1);
            try {
                $this->conn->write($command . "\n");
                $out = $this->conn->read();
                return explode("\n",$out);
            } catch (\Throwable $th) {
                if(isset($this->conn)){
                    $this->conn->disconnect();
                }
                return false;
            }
        } else {
            false;
        }
    }

    function runiShell($command = null,$as_array = true,$marker = '--More--',$maxLoop = 1000)
    {
        $out = '';
        $results = [];
        $safe = 0;

        if (isset($this->conn) && isset($command)) {
            sleep(1);
            try {

                $this->conn->write($command . "\n");
                $output = $this->conn->read();
                $out .= $output;
                $results[] = $output;  
                while(strpos($output, $marker) !== false){
                    $this->conn->write(" ");
                    $output = $this->conn->read();
                    $out .= $output;
                    $results[] = $output;
                    $safe++;
                    if($safe > $maxLoop){
                         break;
                    }
                }
                if($as_array){
                    return explode("\n",$out);
                }else{
                    return $out;
                }

            } catch (\Throwable $th) {
                try {
                    $this->conn->disconnect();
                } catch (\Throwable $th) {
                    if(isset($this->conn)){
                        $this->conn->disconnect();
                    }
                }
                return false;
            }
        } else {
            false;
        }
    }
    
    function runCmd($command = null)
    {
        if (isset($this->conn) && isset($command)) {
            return explode("\n", $this->conn->exec($_POST['cmd']));
        } else {
            return false;
        }
    }

}
