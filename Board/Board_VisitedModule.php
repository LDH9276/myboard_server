<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// GET으로 받아오기
$userId = $_POST['user_id'] ?? '';

$userCheck = $conn->prepare("SELECT visited_board FROM app_users WHERE id = ?");
$userCheck->bind_param("s", $userId);
$userCheck->execute();
$user_result = $userCheck->get_result();

// 유저 정보에서 방문 기록은 , 으로 구분된다.
// 방문 기록이 없을 경우에는 빈 문자열이다.
$visited_board = $user_result->fetch_assoc()['visited_board'] ?? '';

if ($visited_board == null) {
  echo json_encode(array("result" => "null"));
  return;
} else {
    $visited_board = explode(",", $visited_board);  

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
    $userCheck->close();
    $conn->close();

    return;
}
?>