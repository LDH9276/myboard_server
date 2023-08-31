<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST로 받아오기
$id = $_GET['id'] ?? '';

// 리스트 출력하기
$stmt = $conn->prepare("SELECT * FROM app_board WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$list = array();

// DB에서 받아온 데이터를 배열에 저장하기
while ($row = $result->fetch_assoc()) {
  $content = $row['content'];
  preg_match('/<p>(.+?)<\/p>/', $content, $matches);
  if (isset($matches[1])){
    $url = $matches[1];
    // 트위터라면
    if (strpos($url, 'twitter.com') !== false) {
      $twitter_url = 'https://publish.twitter.com/oembed?url=' . $url;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $twitter_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $twitter_json = curl_exec($ch);
      curl_close($ch);

      $twitter_array = json_decode($twitter_json, true);
      $twitter_html = $twitter_array['html'];
      $total_content = $twitter_html . $content;
    } 
    // 유튜브라면
    else if (strpos($url, 'youtube.com') !== false) {
      $youtube_url = 'https://www.youtube.com/oembed?url=' . $url;
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $youtube_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $youtube_json = curl_exec($ch);
      curl_close($ch);

      $youtube_array = json_decode($youtube_json, true);
      $youtube_html = $youtube_array['html'];
      $total_content = '<div class="content-youtube">' . $youtube_html . '</div>' . $content;
    } else {
      $total_content = $content;
    }
  } else {
    $total_content = $content;
  }

  $list[] = array(
    "id" => $row['id'],
    "board_id" => $row['board_id'],
    "cat" => $row['cat'],
    "title" => $row['title'],
    "content" => $total_content,
    "writer" => $row['writer'],
    "comment_count" => $row['comment_count'],
    "total_like" => $row['total_like'],
    "reg_date" => $row['reg_date'],
    "update_date" => $row['update_date']
  );
}


// JSON으로 echo 처리하기
echo json_encode(array("list" => $list));

// DB 처리 종료
$stmt->close();
$conn->close();
?>