<?php


// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// GET으로 페이지 수 받아오기
$page = $_GET['page'] ?? '';
$board_id = $_GET['board'] ?? '';
$board_cate = $_GET['boardCate'] ?? '';
$page = (int)$page;
if ($board_cate != '*'){
  $board_id = (int)$board_id;
}

$page = $page - 1;
$page = $page * 10;

// 리스트 출력하기
if($board_cate == '*'){
  $stmt = $conn->prepare("SELECT * FROM app_board where board_id = ? and not cat = 0 ORDER BY id DESC LIMIT 10 OFFSET ?");
  $stmt->bind_param("ii", $board_id, $page);
} else {
  $stmt = $conn->prepare("SELECT * FROM app_board where board_id = ? and cat = ? ORDER BY id DESC LIMIT 10 OFFSET ?");
  $stmt->bind_param("iii", $board_id, $board_cate, $page);
}
$stmt->execute();
$result = $stmt->get_result();

$update_comment_SQL = "UPDATE app_board b
SET b.comment_count = (
  SELECT COUNT(*) 
  FROM app_comment c 
  WHERE c.post_id = b.id
);";
$update_comment_result = mysqli_query($conn, $update_comment_SQL);



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
  $nickname = $row['nickname'] ?? '';
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
echo json_encode(array("list" => $list));



// DB 처리 종료
$stmt->close();
$conn->close();
?>