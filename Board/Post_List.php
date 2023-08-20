<?php


// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// GET으로 페이지 수 받아오기
$page = $_GET['page'] ?? '';
$page = (int)$page;

$page = $page - 1;
$page = $page * 10;

// 리스트 출력하기
$stmt = $conn->prepare("SELECT * FROM app_board ORDER BY id DESC limit ?, 10");
$stmt->bind_param("i", $page);
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