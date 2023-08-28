<?php

// DB 연결
include_once '../../cors.php';
include_once '../../dbconn.php';

$board_id = $_GET['board'] ?? '';
$board_id = (int)$board_id;

// 총 페이지수 검색하기
$stmt = $conn->prepare("SELECT count(*) FROM app_board where board_id = ?");
$stmt->bind_param("i", $board_id);
$stmt->execute();
$result = $stmt->get_result(); 
$total = mysqli_fetch_array($result); 
$total_pages = $total[0] ?? ''; 

// JSON으로 echo 처리하기
echo json_encode(array("total" => $total_pages));
?>