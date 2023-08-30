<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST
$board_id = $_POST['board_id'] ?? '';
$board_id = (int)$board_id;

$user_id = $_POST['user_id'] ?? '';
$user_id = mysqli_real_escape_string($conn, $user_id);
$mode = $_POST['mode'] ?? null;

if($mode === 'read'){
    $user_subscribe_check = $conn->prepare("SELECT * FROM app_subscribe WHERE user_id = ? AND board_id = ? and is_unsubscribe = 0");
    $user_subscribe_check->bind_param("si", $user_id, $board_id);
    $user_subscribe_check->execute();
    $result = $user_subscribe_check->get_result();

    $board_subscriber_check = $conn->prepare("SELECT board_subscriber FROM app_boardlist WHERE id = ?");
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

if($mode === 'list_read'){
    $user_subscribe_check = $conn->prepare("SELECT * FROM app_subscribe WHERE user_id = ? and is_unsubscribe = 0");
    $user_subscribe_check->bind_param("s", $user_id);
    $user_subscribe_check->execute();
    $result = $user_subscribe_check->get_result();

    $user_subscribe_list = array();
    while($row = mysqli_fetch_array($result)) {

        $board_id = $row['board_id'];
        $board_name_check = $conn->prepare("SELECT board_name FROM app_boardlist WHERE id = ?");
        $board_name_check->bind_param("i", $board_id);
        $board_name_check->execute();
        $row2 = $board_name_check->get_result()->fetch_assoc();
        $board_name = $row2['board_name'] ?? '';

        $user_subscribe_list[] = array(
            'board_id' => $row['board_id'],
            'board_name' => $board_name
        );

        echo json_encode(array("user_subscribe_list" => $user_subscribe_list));
    }
}

else if($mode === 'subscribe'){
    $user_subscribe_check = $conn->prepare("SELECT * FROM app_subscribe WHERE user_id = ? AND board_id = ?");
    $user_subscribe_check->bind_param("si", $user_id, $board_id);
    $user_subscribe_check->execute();
    $result = $user_subscribe_check->get_result();

    $boaard_subscribe_check = $conn->prepare("SELECT board_subscriber FROM app_boardlist WHERE id = ?");
    $boaard_subscribe_check->bind_param("i", $board_id);
    $boaard_subscribe_check->execute();
    $result2 = $boaard_subscribe_check->get_result();
    $row = $result2->fetch_assoc();
    $board_subscriber = $row['board_subscriber'] ?? 0;

    if ($result->num_rows === 0) {
        $user_subscribe = $conn->prepare("INSERT INTO app_subscribe (user_id, board_id) VALUES (?, ?)");
        $user_subscribe->bind_param("si", $user_id, $board_id);
        $user_subscribe->execute();

        echo json_encode([
            'message' => '구독이 완료되었습니다.',
            'is_subscribe' => true
        ]);

        $board_subscriber = $board_subscriber + 1;
        $board_subscriber_update = $conn->prepare("UPDATE app_boardlist SET board_subscriber = ? WHERE id = ?");
        $board_subscriber_update->bind_param("ii", $board_subscriber, $board_id);
        $board_subscriber_update->execute();
    } else {
        $user_subscribe_update = $conn->prepare("UPDATE app_subscribe SET is_unsubscribe = 0 WHERE user_id = ? AND board_id = ?");
        $user_subscribe_update->bind_param("si", $user_id, $board_id);
        $user_subscribe_update->execute();

        echo json_encode([
            'message' => '구독이 완료되었습니다.',
            'is_subscribe' => true
        ]);
        
        $board_subscriber = $board_subscriber + 1;
        $board_subscriber_update = $conn->prepare("UPDATE app_boardlist SET board_subscriber = ? WHERE id = ?");
        $board_subscriber_update->bind_param("ii", $board_subscriber, $board_id);
        $board_subscriber_update->execute();
    }
}

else if($mode === 'unsubscribe'){
    $user_subscribe_check = $conn->prepare("SELECT * FROM app_subscribe WHERE user_id = ? AND board_id = ?");
    $user_subscribe_check->bind_param("si", $user_id, $board_id);
    $user_subscribe_check->execute();
    $result = $user_subscribe_check->get_result();

    $boaard_subscribe_check = $conn->prepare("SELECT board_subscriber FROM app_boardlist WHERE id = ?");
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
        $user_subscribe_update = $conn->prepare("UPDATE app_subscribe SET is_unsubscribe = 1 WHERE user_id = ? AND board_id = ?");
        $user_subscribe_update->bind_param("si", $user_id, $board_id);
        $user_subscribe_update->execute();

        echo json_encode([
            'message' => '구독이 취소되었습니다.',
            'is_subscribe' => false
        ]);

        $board_subscriber = $board_subscriber - 1;
        $board_subscriber_update = $conn->prepare("UPDATE app_boardlist SET board_subscriber = ? WHERE id = ?");
        $board_subscriber_update->bind_param("ii", $board_subscriber, $board_id);
        $board_subscriber_update->execute();
    }
}

else if($mode === null){
    echo json_encode([
        'message' => '잘못된 접근입니다.'
    ]);
}

// DB
$conn->close();

?>