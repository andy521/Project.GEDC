<?php namespace App\Console\Commands;

use App\DailyData;
use App\HourlyData;
use App\MonthlyData;
use App\SensorData;
use DateTime;
use ErrorException;
use Illuminate\Console\Command;

error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

class SocketCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'socket:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start socket server.';

    /**
     * The socket binding address
     *
     * @var string
     */
    protected $address = '0.0.0.0';

    /**
     * The socket binding port
     *
     * @var int
     */
    protected $port = 10000;
    
    public function __construct() {
        parent::__construct();
        $this->port = env('SOCKET_PORT', 10000);
    }

    public static function microtime_as_long() {
        return (integer)round(microtime(true) * 1000);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire() {
        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        }
        if (socket_bind($sock, $this->address, $this->port) === false) {
            echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        }
        if (socket_listen($sock, 5) === false) {
            echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        }
        do {
            if (($msgsock = socket_accept($sock)) === false) {
                echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
                break;
            }
            socket_getpeername($msgsock, $IP, $PORT);
            try {
                if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
                    echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                    break;
                }
                $t_on_receive = self::microtime_as_long();
                if ($buf = trim($buf)) {
                    $time = date('Y-m-d H:i:s');
                    echo "[$time][Receive][$IP:$PORT] $buf\n";
                    $talkback = $this->process($buf, $t_on_receive) . "\n";
                    socket_write($msgsock, $talkback, strlen($talkback));
                    $time = date('Y-m-d H:i:s');
                    echo "[$time][Answer][$IP:$PORT] $talkback\n";
                }
            } catch (\Exception $e) {
                echo $e;
            } finally {
                socket_close($msgsock);
            }
        } while (true);

        socket_close($sock);
    }

    /**
     * Parse and save sensor data
     *
     * @param $data string
     * @return bool
     */
    protected function parseData($data) {
        $parsed = json_decode($data);
        if ($parsed && count($parsed) == 9) {
            $sensorData = new SensorData;
            $sensorData->sensor_id = $parsed[0];
            $sensorData->acv = $parsed[2];
            $sensorData->acx = $parsed[3];
            $sensorData->acy = $parsed[4];
            $sensorData->acz = $parsed[5];
            $sensorData->ibi = $parsed[6];
            $sensorData->bpm = $parsed[7];
            $sensorData->tem = $parsed[8];

            // Accept 10 or 13 digits timestamp
            $timestamp = $parsed[1];
            $digits = strlen(floor($timestamp));
            switch ($digits) {
                case 10: break;
                case 13: $timestamp = $timestamp / 1000; break;
                default: return false;
            }
            $date = new DateTime();
            $date->setTimestamp($timestamp);
            $sensorData->timestamp = $date;

            if ($sensorData->save()) {
                HourlyData::saveOrUpdateOnSensorData($sensorData);
                DailyData::saveOrUpdateOnSensorData($sensorData);
                MonthlyData::saveOrUpdateOnSensorData($sensorData);
                return true;
            }
        }
        return false;
    }

    protected function process($data, $t_on_receive) {
        switch (substr($data, 0, 2)) {
            case 'd:':
                return ($this->parseData(substr($data, 2)) ? "success" : "failed") . ": $data";
            case 't:':
                return substr($data, 2).",$t_on_receive,".self::microtime_as_long();
        }
        return "invalid data";
    }

}