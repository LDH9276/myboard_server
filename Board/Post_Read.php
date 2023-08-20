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

// 리스트 반복처리 후 JSON 데이터로 변환
$list = array();

while($row = mysqli_fetch_array($result)) {
  $id = $row['id'] ?? '';
  $title = $row['title'] ?? '';
  $content = $row['content'] ?? '';
  $writer = $row['writer'] ?? '';
  $reg_date = $row['reg_date'] ?? '';

  $list[] = array(
    'id' => $id,
    'title' => $title,
    'content' => $content,
    'writer' => $writer,
    'reg_date' => $reg_date
  );
}


// JSON으로 echo 처리하기
echo json_encode(array("list" => $list));

// DB 처리 종료
$stmt->close();
$conn->close();
?>