<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST로 받아오기
$id = $_POST['id'] ?? '';
$user_id = $_POST['user_id'] ?? '';

// 실제로 좋아요가 되어 있는지 확인
$stmt = $conn->prepare("SELECT * FROM app_like WHERE post_id = ? AND user_id = ? AND is_delete = false");
$stmt->bind_param("is", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 좋아요가 되어 있으면 JSON 전달
if($result->num_rows == 0) {
  echo json_encode(array("like_status" => false));
} else {
  echo json_encode(array("like_status" => true));
}