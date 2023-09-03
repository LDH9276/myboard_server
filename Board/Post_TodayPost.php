<?php


// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// get으로 받아오기
$board_id = $_GET['board'] ?? '';
$board_id = (int)$board_id;

// 좋아요수가 많은 순으로 정렬
$stmt = $conn->prepare("SELECT * FROM app_board where board_id = ? ORDER BY total_like DESC limit 5");
$stmt->bind_param("i", $board_id);
$stmt->execute();
$result = $stmt->get_result();

// 리스트 반복처리 후 JSON 데이터로 변환
$list = array();

while($row = mysqli_fetch_array($result)) {
  $id = $row['id'] ?? '';
  $cat = $row['cat'] ?? '';
  $nickname = $row['nickname'] ?? '';
  $title = $row['title'] ?? '';
  $content = $row['content'] ?? '';
  $writer = $row['writer'] ?? '';
  $reg_date = $row['reg_date'] ?? '';
  $comment_count = $row['comment_count'] ?? '';
  $total_like = $row['total_like'] ?? '';

  $stmt_profile = $conn->prepare("SELECT profile_img FROM app_users WHERE id = ?");
  $stmt_profile->bind_param("s", $writer);
  $stmt_profile->execute();
  $profile_result = $stmt_profile->get_result();
  $row2 = mysqli_fetch_array($profile_result);
  $profile_img = $row2['profile_img'] ?? '';
  if ($profile_img == '') {
    $profile_img = 'defaultprofile.png';
    $profile_img = explode('.', $profile_img);
  } else {
    $profile_img = explode('.', $profile_img);
  }

  $list[] = array(
    'id' => $id,
    'cat' => $cat,
    'nickname' => $nickname,
    'title' => $title,
    'profile_imgname' => $profile_img[0],
    'profile_img' => $profile_img[1],
    'writer' => $writer,
    'reg_date' => $reg_date,
    'content' => $content,
    'comment_count' => $comment_count,
    'total_like' => $total_like
  );
}

// JSON으로 echo 처리하기
echo json_encode(array("today_postlist" => $list));
