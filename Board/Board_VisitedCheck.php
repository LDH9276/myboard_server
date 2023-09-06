<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// GET으로 받아오기
$boardId = $_POST['board_id'] ?? '';
$userId = $_POST['user_id'] ?? '';

$userCheck = $conn->prepare("SELECT visited_board FROM app_users WHERE id = ?");
$userCheck->bind_param("s", $userId);
$userCheck->execute();
$user_result = $userCheck->get_result();



// 유저 정보에서 방문 기록은 , 으로 구분된다.
// 방문 기록이 없을 경우에는 빈 문자열이다.
$visited_board = $user_result->fetch_assoc()['visited_board'] ?? '';

// 방문기록이 null 이라면 insert into
if ($visited_board == null) {
    $stmt = $conn->prepare("UPDATE app_users SET visited_board = ? WHERE id = ?");
    $stmt->bind_param("ss", $boardId, $userId);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(array("result" => "success"));
    return;
}

$visited_board = explode(",", $visited_board);

// 중복 제거
$visited_board = array_unique($visited_board);

// 만약 배열에 $boardId가 있다면 맨 위의 순서로 옮긴다.
if (in_array($boardId, $visited_board)) {
  $visited_board = array_diff($visited_board, array($boardId));
  $visited_board = array_merge(array($boardId), $visited_board);
}

// 배열에 $boardId가 없다면 $boardId를 맨 위의 순서로 추가한다.
else {
  $visited_board = array_merge(array($boardId), $visited_board);
}

// 배열을 다시 문자열로 바꾼다.
$visited_board = implode(",", $visited_board);

// 유저 정보에 방문 기록을 업데이트한다.
$stmt = $conn->prepare("UPDATE app_users SET visited_board = ? WHERE id = ?");
$stmt->bind_param("ss", $visited_board, $userId);
$stmt->execute();

?>
