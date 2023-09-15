<?php

include_once '../cors.php';
include_once '../dbconn.php';

class SubscriptionController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function read($board_id, $user_id) {
        $user_subscribe_check = $this->conn->prepare("SELECT * FROM app_subscribe WHERE user_id = ? AND board_id = ? and is_unsubscribe = 0");
        $user_subscribe_check->bind_param("si", $user_id, $board_id);
        $user_subscribe_check->execute();
        $result = $user_subscribe_check->get_result();

        $board_subscriber_check = $this->conn->prepare("SELECT board_subscriber FROM app_boardlist WHERE id = ?");
        $board_subscriber_check->bind_param("i", $board_id);
        $board_subscriber_check->execute();
        $row = $board_subscriber_check->get_result()->fetch_assoc();
        $board_subscriber = $row['board_subscriber'] ?? 0;

        if ($result->num_rows === 0) {
            $message = '구독하지 않은 게시판입니다.';
            $is_subscribe = false;
        } else {
            $message = '구독한 게시판입니다.';
            $is_subscribe = true;
        }

        $response = [
            'message' => $message,
            'is_subscribe' => $is_subscribe,
            'board_subscriber' => $board_subscriber
        ];

        echo json_encode($response);
    }

    public function listRead($user_id) {
        $user_subscribe_check = $this->conn->prepare("SELECT * FROM app_subscribe WHERE user_id = ? AND is_unsubscribe = 0");
        $user_subscribe_check->bind_param("s", $user_id);
        $user_subscribe_check->execute();
        $result = $user_subscribe_check->get_result();

        while ($row = $result->fetch_assoc()) {
            $board_id = $row['board_id'] ?? '0';
            $board_id = (int)$board_id;

            $board_subscriber_check = $this->conn->prepare("SELECT board_name FROM app_boardlist WHERE id = ?");
            $board_subscriber_check->bind_param("i", $board_id);
            $board_subscriber_check->execute();
            $row2 = $board_subscriber_check->get_result()->fetch_assoc();
            $board_name = $row2['board_name'] ?? 0;

            $boardlist[] = [
                'board_id' => $board_id,
                'board_name' => $board_name
            ];
        }

        echo json_encode(["subscribe" => $boardlist]);
    }

    public function subscribe($board_id, $user_id) {
        $user_subscribe_check = $this->conn->prepare("SELECT * FROM app_subscribe WHERE user_id = ? AND board_id = ?");
        $user_subscribe_check->bind_param("si", $user_id, $board_id);
        $user_subscribe_check->execute();
        $result = $user_subscribe_check->get_result();

        $boaard_subscribe_check = $this->conn->prepare("SELECT board_subscriber FROM app_boardlist WHERE id = ?");
        $boaard_subscribe_check->bind_param("i", $board_id);
        $boaard_subscribe_check->execute();
        $result2 = $boaard_subscribe_check->get_result();
        $row = $result2->fetch_assoc();
        $board_subscriber = $row['board_subscriber'] ?? 0;

        if ($result->num_rows === 0) {
            $user_subscribe = $this->conn->prepare("INSERT INTO app_subscribe (user_id, board_id) VALUES (?, ?)");
            $user_subscribe->bind_param("si", $user_id, $board_id);
            $user_subscribe->execute();

            echo json_encode([
                'message' => '구독이 완료되었습니다.',
                'is_subscribe' => true
            ]);

            $board_subscriber = $board_subscriber + 1;
            $board_subscriber_update = $this->conn->prepare("UPDATE app_boardlist SET board_subscriber = ? WHERE id = ?");
            $board_subscriber_update->bind_param("ii", $board_subscriber, $board_id);
            $board_subscriber_update->execute();
        } else {
            $user_subscribe_update = $this->conn->prepare("UPDATE app_subscribe SET is_unsubscribe = 0 WHERE user_id = ? AND board_id = ?");
            $user_subscribe_update->bind_param("si", $user_id, $board_id);
            $user_subscribe_update->execute();

            echo json_encode([
                'message' => '구독이 완료되었습니다.',
                'is_subscribe' => true
            ]);

            $board_subscriber = $board_subscriber + 1;
            $board_subscriber_update = $this->conn->prepare("UPDATE app_boardlist SET board_subscriber = ? WHERE id = ?");
            $board_subscriber_update->bind_param("ii", $board_subscriber, $board_id);
            $board_subscriber_update->execute();
        }
    }

    public function unsubscribe($board_id, $user_id) {
        $user_subscribe_check = $this->conn->prepare("SELECT * FROM app_subscribe WHERE user_id = ? AND board_id = ?");
        $user_subscribe_check->bind_param("si", $user_id, $board_id);
        $user_subscribe_check->execute();
        $result = $user_subscribe_check->get_result();

        $boaard_subscribe_check = $this->conn->prepare("SELECT board_subscriber FROM app_boardlist WHERE id = ?");
        $boaard_subscribe_check->bind_param("i", $board_id);
        $boaard_subscribe_check->execute();
        $result2 = $boaard_subscribe_check->get_result();
        $row = $result2->fetch_assoc();
        $board_subscriber = $row['board_subscriber'] ?? 0;

        if ($result->num_rows === 0) {
            echo json_encode([
                'message' => '구독하지 않은 게시판입니다.',
                'is_subscribe' => false
            ]);
        } else {
            $user_subscribe_update = $this->conn->prepare("UPDATE app_subscribe SET is_unsubscribe = 1 WHERE user_id = ? AND board_id = ?");
            $user_subscribe_update->bind_param("si", $user_id, $board_id);
            $user_subscribe_update->execute();

            echo json_encode([
                'message' => '구독이 취소되었습니다.',
                'is_subscribe' => false
            ]);

            $board_subscriber = $board_subscriber - 1;
            $board_subscriber_update = $this->conn->prepare("UPDATE app_boardlist SET board_subscriber = ? WHERE id = ?");
            $board_subscriber_update->bind_param("ii", $board_subscriber, $board_id);
            $board_subscriber_update->execute();
        }
    }
}

$mode = $_POST['mode'] ?? null;
$board_id = $_POST['board_id'] ?? '';
$board_id = (int)$board_id;
$user_id = $_POST['user_id'] ?? '';
$user_id = mysqli_real_escape_string($conn, $user_id);

$controller = new SubscriptionController($conn);

if ($mode === 'read') {
    $controller->read($board_id, $user_id);
} else if ($mode === 'list_read') {
    $controller->listRead($user_id);
} else if ($mode === 'subscribe') {
    $controller->subscribe($board_id, $user_id);
} else if ($mode === 'unsubscribe') {
    $controller->unsubscribe($board_id, $user_id);
} else {
    echo json_encode([
        'message' => '잘못된 접근입니다.'
    ]);
}

$conn->close();

?>