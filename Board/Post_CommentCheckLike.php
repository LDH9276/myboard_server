<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST로 받아오기
$post_id = $_POST['post_id'] ?? 0;
$id = (int)$post_id;
$id = $_POST['id'] ?? '';
$user_id = $_POST['user_id'] ?? '';

// 실제로 좋아요가 되어 있는지 확인
$stmt = $conn->prepare("SELECT * FROM app_comment_like WHERE post_id = ? AND user_id = ?");
$stmt->bind_param("is", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = mysqli_fetch_array($result)) {
  $comment_id = $row['comment_id'] ?? 0;
  $list[] = array(
    'id' => $comment_id
  );
}

echo json_encode(array("like_comment_list" => $list));