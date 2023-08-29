<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST로 받아오기
$id = $_GET['id'] ?? '';

// 리스트 출력하기
$stmt = $conn->prepare("SELECT * FROM app_board WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$list = array();

// DB에서 받아온 데이터를 배열에 저장하기
while ($row = $result->fetch_assoc()) {
  $content = $row['content'];


  $list[] = array(
    "id" => $row['id'],
    "board_id" => $row['board_id'],
    "cat" => $row['cat'],
    "title" => $row['title'],
    "content" => $content,
    "writer" => $row['writer'],
    "comment_count" => $row['comment_count'],
    "total_like" => $row['total_like'],
    "reg_date" => $row['reg_date'],
    "update_date" => $row['update_date']
  );
}


// JSON으로 echo 처리하기
echo json_encode(array("list" => $list));

// DB 처리 종료
$stmt->close();
$conn->close();
?>