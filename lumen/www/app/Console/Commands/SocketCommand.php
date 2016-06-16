<?php namespace App\Console\Commands;

use App\DailyData;
use App\HourlyData;
use App\MonthlyData;
use App\Notification;
use App\SensorData;
use DateTime;
use Illuminate\Console\Command;
use Pusher;

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

    /**
     * The Pusher.com service
     *
     * @var null|Pusher
     */
    protected $pusher = null;
    
    public function __construct() {
        parent::__construct();
        $options = array('encrypted' => true);
        $this->pusher = new Pusher('24737bc4b88e96c1898c', 'd6ab2f39949df87df8fa', '213316', $options);
        $this->port = env('SOCKET_PORT', 10000);
    }

    private static function microtime_as_long() {
        return (integer)round(microtime(true) * 1000);
    }

    private static function unix_time_to_datetime($timestamp) {
        $digits = strlen(floor($timestamp));
        switch ($digits) {
            case 10: break;
            case 13: $timestamp = $timestamp / 1000; break;
            default: return false;
        }
        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);
        return $datetime;
    }

    /**
     * Parse and save sensor data
     *
     * @param $data string
     * @return bool
     */
    private function parseData($data) {
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
            $timestamp = self::unix_time_to_datetime($parsed[1]);
            if (!$timestamp) {
                return false;
            }
            $sensorData->timestamp = $timestamp;

            if ($sensorData->save()) {
                HourlyData::saveOrUpdateOnSensorData($sensorData);
                DailyData::saveOrUpdateOnSensorData($sensorData);
                MonthlyData::saveOrUpdateOnSensorData($sensorData);
                return true;
            }
        }
        return false;
    }

    /**
     * Parse data and push notification to Pusher.com
     * @param $data string
     * @return bool
     */
    private function pushNotification($data) {
        $parsed = json_decode($data);
        if ($parsed && count($parsed) == 4) {
            $sensorId   = $parsed[0];
            $timestamp  = self::unix_time_to_datetime($parsed[1]);
            $type       = $parsed[2];
            $append     = $parsed[3];
            if (!$timestamp) {
                return false;
            }
            $message = [
                'sensorId' => $sensorId,
                'timestamp' => $timestamp->format("Y-m-d H:i:s"),
            ];
            switch ($type) {
                case 0:
                    $channel = 'alert_channel';
                    $event = 'new_alert';
                    switch ($append) {
                        case 0: $message['type'] = 'fall'; break;
                    }
                    break;
                default: return false;
            }

            $notification = new Notification;
            $notification->sensor_id = $sensorId;
            $notification->timestamp = $timestamp;
            $notification->type = $type;
            $notification->append = $append;
            $notification->save();

            return $this->pusher->trigger($channel, $event, $message);
        }
        return false;
    }

    private function process($data, $t_on_receive) {
        switch (substr($data, 0, 2)) {
            case 'd:':
                return ($this->parseData(substr($data, 2)) ? "success" : "failed") . ": $data";
            case 't:':
                return substr($data, 2).",$t_on_receive,".self::microtime_as_long();
            case 'n:':
                return ($this->pushNotification(substr($data, 2)) ? "success" : "failed") . ": $data";
        }
        return "invalid data";
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire() {
        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
            return;
        }
        if (socket_bind($sock, $this->address, $this->port) === false) {
            echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
            return;
        }
        if (socket_listen($sock, 5) === false) {
            echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
            return;
        }
        if (socket_set_nonblock($sock) === false) {
            echo "socket_set_nonblock() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
            return;
        }
        do {
            if (($msgsock = @socket_accept($sock)) === false) {
                continue;
            }
            try {
                socket_getpeername($msgsock, $IP, $PORT);
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

}