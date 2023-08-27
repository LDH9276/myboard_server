<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// GET으로 페이지 수 받아오기
$page = $_GET['page'] ?? '';
$page = (int)$page;

$page = $page - 1;
$page = $page * 10;

// 작성글 id 배열 만들기
$stmt = $conn->prepare("SELECT id FROM app_board ORDER BY id DESC limit ?, 10");
$stmt->bind_param("i", $page);
$stmt->execute();
$result = $stmt->get_result();

$id_list = array();

while($row = mysqli_fetch_array($result)) {
  $id = $row['id'] ?? '';
  $id_list[] = $id;
}

// 댓글 리스트 출력하기
$list = array();

foreach ($id_list as $id) {
  $stmt = $conn->prepare("SELECT * FROM app_comment WHERE post_id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  while($row = mysqli_fetch_array($result)) {

    if ($row['is_deleted'] == 1) {
      $id = $row['id'] ?? '';
      $content = '삭제된 댓글입니다.';
      $writer = '';
      $reg_date = '';
      $commnet_parent = $row['comment_parent'] ?? null;
      $comment_depth = $row['comment_depth'] ?? '';
      $is_deleted = 1;
    } else {
      $id = $row['id'] ?? '';
      $content = $row['content'] ?? '';
      $writer = $row['writer'] ?? '';
      $reg_date = $row['reg_date'] ?? '';
      $commnet_parent = $row['comment_parent'] ?? null;
      $comment_depth = $row['comment_depth'] ?? '';
      $is_deleted = 0;
    }

    $list[] = array(
      'post_id' => $post_id,
      'id' => $id,
      'content' => $content,
      'writer' => $writer,
      'reg_date' => $reg_date,
      'comment_parent' => $commnet_parent,
      'comment_depth' => $comment_depth,
      'is_deleted' => $is_deleted
    );
  }
}

// JSON으로 echo 처리하기
echo json_encode(array("list" => $list));

// DB 처리 종료
$stmt->close();
$conn->close();
?>