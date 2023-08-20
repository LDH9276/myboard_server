<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST 요청으로 받은 데이터
// ID   : 아이디
// data : 글 내용
// file : 파일
$writer = $_POST['writer'] ?? '';
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$reg_date = date("Y-m-d H:i:s");

// 수정모드시 받아올 것들
$id = $_POST['id'] ?? '';
$modify = $_POST['modify'] ?? false;

// SQL 인젝션 방지
$writer = mysqli_real_escape_string($conn, $writer);
$title = mysqli_real_escape_string($conn, $title);
$content = mysqli_real_escape_string($conn, $content);

// 수정모드일 경우에는 ($modify == true)
if ($modify) {
  // 수정모드
  $stmt = $conn->prepare("UPDATE app_board SET title = ?, content = ?, update_date = ? WHERE id = ?");
  
  // 업데이트 날짜
  $update_date = date("Y-m-d H:i:s");
  $stmt->bind_param("sssi", $title, $content, $update_date, $id);
  $stmt->execute();

  // DB 처리 종료
  $stmt->close();
  $conn->close();
} 

// 수정사항이 아닐 경우에는
else {

// DB에 글 저장
$stmt = $conn->prepare("INSERT INTO app_board (writer, title, content, reg_date) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $writer, $title, $content, $reg_date);
$stmt->execute();

// DB 처리 종료
$stmt->close();
$conn->close();
}
?>
