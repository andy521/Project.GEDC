<?php namespace App\Console\Commands;

use App\SensorData;
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
            $msg = "Welcome to the Project-EC Socket Server.\n";
            socket_write($msgsock, $msg, strlen($msg));
            do {
                try {
                    if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
                        echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                        break 2;
                    }
                    if (!$buf = trim($buf)) {
                        continue;
                    }
                    $talkback = "Received: '$buf'.\n";
                    socket_write($msgsock, $talkback, strlen($talkback));
                    $this->handleData($buf);
                    echo "[$IP:$PORT]$buf\n";
                } catch (ErrorException $e) {
                    break;
                }
            } while (true);
            socket_close($msgsock);
        } while (true);

        socket_close($sock);
    }

    /**
     * Parse and save sensor data
     *
     * @param $data string
     */
    protected function handleData($data) {
        $sensorData = new SensorData;
        $sensorData->save();
    }

}