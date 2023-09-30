<?php

// utf-8 인코딩
header('Content-Type: application/json; charset=utf-8');

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// 리스트 출력하기
$stmt = $conn->prepare("SELECT * FROM app_boardlist order by board_subscriber desc");
$stmt->execute();
$result = $stmt->get_result();

// 리스트 반복처리 후 JSON 데이터로 변환
$list = array();

while ($row = mysqli_fetch_array($result)) {
    $id = $row['id'] ?? '';
    $board_name = $row['board_name'] ?? '';
    $board_thumbnail = $row['board_thumb'] ?? '';
    $board_master = $row['board_master'] ?? '';
    $board_maseters = explode(',', $board_master);
    $board_detail = $row['board_detail'] ?? '';
    $board_kind = $row['board_kind'] ?? '';
    $board_category = $row['board_category'] ?? '';
    $board_subscriber = $row['board_subscriber'] ?? '';
    $board_category_item = explode(',', $board_category);



    $list[] = array(
        'id' => $id,
        'board_thumbnail' => $board_thumbnail,
        'board_name' => $board_name,
        'board_admin' => $board_maseters,
        'board_detail' => $board_detail,
        'board_kind' => $board_kind,
        'board_category' => $board_category_item,
        'board_subscriber' => $board_subscriber
    );

}

echo json_encode(array("list" => $list));
