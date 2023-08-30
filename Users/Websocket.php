use Ratchet\App;
use Ratchet\ConnectionInterface;

class MyServer extends App {

    public function onOpen(ConnectionInterface $conn) {
        echo "New connection opened.\n";

        $conn->on('message', function ($data) {
            echo "Received message: $data\n";

            // 특정 값을 받았을 때 웹소켓을 시작합니다.
            if ($data === 'start') {
                $conn->send('Websocket started.');
            }
        });

        $conn->on('close', function () {
            echo "Connection closed.\n";
        });
    }
}

$server = new MyServer();
$server->run();