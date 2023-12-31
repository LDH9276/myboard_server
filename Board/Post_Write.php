<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST 요청으로 받은 데이터
// ID   : 아이디
// data : 글 내용
// file : 파일
$writer = $_POST['writer'] ?? '';
$nickname = $_POST['nickname'] ?? '';
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$nickname = $_POST['nickname'] ?? '';
$category = $_POST['category'] ?? '';
$category = (int)$category;
$board_id = $_POST['board'] ?? '';
$board_id = (int)$board_id;
$reg_date = date("Y-m-d H:i:s");

// 수정모드시 받아올 것들
$id = $_POST['id'] ?? '';
$modify = $_POST['modify'] ?? false;
$delete = $_POST['delete'] ?? false;

// SQL 인젝션 방지
$writer = mysqli_real_escape_string($conn, $writer);
$title = mysqli_real_escape_string($conn, $title);
$content = mysqli_real_escape_string($conn, $content);

// 수정모드일 경우에는 ($modify == true)
if ($modify) {
  // 수정모드
  $stmt = $conn->prepare("UPDATE app_board SET title = ?, content = ?, board_id = ?, update_date = ?, cat = ? WHERE id = ?");
  $update_date = date("Y-m-d H:i:s");
  $stmt->bind_param("ssisii", $title, $content, $board_id, $update_date, $category, $id);
  $stmt->execute();

  var_dump($stmt);

  // DB 처리 종료
  $stmt->close();
  $conn->close();

// 삭제모드일 경우에는 ($delete == true)
} else if (!$modify && $delete) {
  // 삭제모드
  $stmt = $conn->prepare("DELETE FROM app_board WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  // DB 처리 종료
  $stmt->close();
  $conn->close();
}

// 수정사항이 아닐 경우에는
else {

// DB에 글 저장
$stmt = $conn->prepare("INSERT INTO app_board (writer, title, cat, content, nickname, board_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssissi", $writer, $title, $category, $content, $nickname, $board_id);
$stmt->execute();

// DB 처리 종료
$stmt->close();
$conn->close();
}
?>
