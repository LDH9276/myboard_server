<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST로 받아오기
$post_id = $_POST['post_id'] ?? '';
$post_id = (int)$post_id;
$id = $_POST['comment_id'] ?? '';
$id = (int)$id;
$user_id = $_POST['user_id'] ?? '';
$like = $_POST['like'] ?? '';

// 실제로 좋아요가 되어 있는지 확인
$stmt = $conn->prepare("SELECT * FROM app_comment_like WHERE comment_id = ? AND user_id = ?");
$stmt->bind_param("is", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 좋아요가 되어 있지 않다면 레코드 추가
if($result->num_rows == 0) {
  
  $stmt = $conn->prepare("INSERT INTO app_comment_like (post_id, comment_id, user_id) VALUES (?, ?, ?)");
  $stmt->bind_param("iis", $post_id, $id, $user_id);
  $stmt->execute();

  // 좋아요 수 증가
  $stmt = $conn->prepare("UPDATE app_comment SET total_like = total_like + 1 WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  // DB 처리 종료
  $stmt->close();
  $conn->close();

  echo json_encode(array("result" => true));

} else {
  if($like === "false"){
    // is_delete를 true로 변경
    $stmt = $conn->prepare("UPDATE app_comment_like SET is_delete = true WHERE comment_id = ? AND user_id = ?");
    $stmt->bind_param("is", $id, $user_id);
    $stmt->execute();

    // 좋아요 수 감소
    $stmt = $conn->prepare("UPDATE app_comment SET total_like = total_like - 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // DB 처리 종료
    $stmt->close();
    $conn->close();

    echo json_encode(array("result" => false));
  } else {
    // is_delete를 false로 변경
    $stmt = $conn->prepare("UPDATE app_comment_like SET is_delete = false WHERE comment_id = ? AND user_id = ?");
    $stmt->bind_param("is", $id, $user_id);
    $stmt->execute();

    // 좋아요 수 증가
    $stmt = $conn->prepare("UPDATE app_comment SET total_like = total_like + 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // DB 처리 종료
    $stmt->close();
    $conn->close();

    echo json_encode(array("result" => true));
  }
}


?>