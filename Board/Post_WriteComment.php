<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST 요청으로 받은 데이터
// ID   : 아이디
// data : 글 내용
// file : 파일
$writer = $_POST['writer'] ?? '';
$content = $_POST['content'] ?? '';
$post_id = $_POST['post_id'] ?? 0;
$reg_date = date("Y-m-d H:i:s");

// 수정모드시 받아올 것들
$id = $_POST['id'] ?? '';
$modify = $_POST['modify'] ?? false;
$delete = $_POST['delete'] ?? false;

// 답글시 받아올 것들
$comment_id = $_POST['comment_id'] ?? '';
$answer = $_POST['answer'] ?? false;

// SQL 인젝션 방지
$writer = mysqli_real_escape_string($conn, $writer);
$content = mysqli_real_escape_string($conn, $content);

// 수정모드일 경우에는 ($modify == true)
if ($modify && !$answer && !$delete) {
  // 수정모드
  $stmt = $conn->prepare("UPDATE app_comment SET content = ?, update_date = ? WHERE id = ?");
  $update_date = date("Y-m-d H:i:s");
  $stmt->bind_param("ssi", $content, $update_date, $id);
  $stmt->execute();

} else if (!$modify && $answer) {
  // 답글모드
  $depth = $_POST['depth'] ?? 0;
  $comment_depth = $depth + 1;

  $stmt = $conn->prepare("INSERT INTO app_comment (post_id, writer, content, reg_date, comment_parent, comment_depth) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssii", $post_id, $writer, $content, $reg_date, $comment_id, $comment_depth);
  $stmt->execute();

  // 게시판의 댓글 수 증가
  $stmt = $conn->prepare("UPDATE app_board SET comment_count = comment_count + 1 WHERE id = ?");
  $stmt->bind_param("i", $post_id);
  $stmt->execute();

// 삭제모드일 경우에는 ($delete == true)
} else if (!$modify && $delete) {
  // 삭제모드
  $stmt = $conn->prepare("UPDATE app_comment SET is_deleted = 1 WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  // 게시판의 댓글 수 감소
  $stmt = $conn->prepare("UPDATE app_board SET comment_count = comment_count - 1 WHERE id = ?");
  $stmt->bind_param("i", $post_id);
  $stmt->execute();

}

// 수정사항이 아닐 경우에는
else {

// DB에 글 저장
$stmt = $conn->prepare("INSERT INTO app_comment (post_id, writer, content, reg_date) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $post_id, $writer, $content, $reg_date);
$stmt->execute();

}

// 댓글 수 최신화
$stmt = $conn->prepare("UPDATE app_board SET comment_count = (SELECT COUNT(*) FROM app_comment WHERE app_comment.post_id = app_board.id AND app_comment.is_deleted = ?)");
$is_deleted = 0;
$stmt->bind_param("i", $is_deleted);
$stmt->execute();

// DB 처리 종료
$stmt->close();
$conn->close();

?>
