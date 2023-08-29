<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// GET으로 받아오기
$userId = $_POST['user_id'] ?? [];

$visited_board = $userId;

if ($visited_board == null) {
  echo json_encode(array("result" => "null"));
  return;
} else {
  $visited_board = json_decode($userId, true);
  $visited_board = array_map('intval', $visited_board);

    $list = array();
    foreach ($visited_board as $board_id) {
        $listChek = $conn->prepare("SELECT * FROM app_boardlist WHERE id = ?");
        $listChek->bind_param("i", $board_id);
        $listChek->execute();
        $list_result = $listChek->get_result()->fetch_assoc();

        $list[] = array(
            "id" => $list_result['id'],
            "board_name" => $list_result['board_name'],
        );
    }

    echo json_encode(array("result" => $list));

    // DB 처리 종료
    $listChek->close();
    $conn->close();

    return;
}
?>