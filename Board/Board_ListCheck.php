<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// GET으로 받아오기
$id = $_GET['id'] ?? '';
$id = (int)$id;

// 리스트 출력하기
$stmt = $conn->prepare("SELECT * FROM app_boardlist WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// 리스트 반복처리 후 JSON 데이터로 변환
$list = array();

while($row = mysqli_fetch_array($result)) {
  $id = $row['id'] ?? '';
  $board_name = $row['board_name'] ?? '';
  $board_thumbnail = $row['board_thumb'] ?? '';
  $board_detail = $row['board_detail'] ?? '';
  $board_kind = $row['board_kind'] ?? '';
  $board_category = $row['board_category'] ?? '';
  $board_category_item = explode(',', $board_category);



  $list[] = array(
    'id' => $id,
    'board_thumbnail' => $board_thumbnail,
    'board_name' => $board_name,
    'board_detail' => $board_detail,
    'board_kind' => $board_kind,
    'board_category' => $board_category_item
  );

  echo json_encode(array("boardlist" => $list));

}




