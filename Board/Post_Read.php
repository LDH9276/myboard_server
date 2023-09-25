<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST로 받아오기
$id = $_POST['id'] ?? '';
$mod = $_POST['mod'] ?? false;

// 리스트 출력하기
$stmt = $conn->prepare("SELECT * FROM app_board WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$list = array();

if($mod){
  while ($row = $result->fetch_assoc()) {
    $content = $row['content'];
    $total_content = $content;

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
} else {
  // DB에서 받아온 데이터를 배열에 저장하기
  while ($row = $result->fetch_assoc()) {
    $content = $row['content'];
    $total_content = $content;

    preg_match_all('/<p>(.+?)<\/p>/', $content, $matches);
    $total_content = $content;
    
    foreach ($matches[1] as $url) {
        if (strpos($url, 'youtube.com') !== false) {
            $youtube_url = 'https://www.youtube.com/oembed?url=' . $url;
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $youtube_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL 인증서 검증 무시
            $youtube_json = curl_exec($ch);
            curl_close($ch);
    
            $youtube_array = json_decode($youtube_json, true);
            $youtube_html = $youtube_array['html'];
    
            // $content에서 유튜브 URL을 제거하고 대신 유튜브 oEmbed HTML을 삽입
            $total_content = str_replace('<p>' . $url . '</p>', '<div class="content-youtube">' . $youtube_html . '</div>', $total_content);
        } else if (strpos($url, 'twitter.com') !== false) {
            $twitter_url = 'https://publish.twitter.com/oembed?url=' . $url;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $twitter_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL 인증서 검증 무시
            $twitter_json = curl_exec($ch);
            curl_close($ch);

            $twitter_array = json_decode($twitter_json, true);
            $twitter_html = $twitter_array['html'];

            // $content에서 트위터 URL을 제거하고 대신 트위터 oEmbed HTML을 삽입
            $total_content = str_replace('<p>' . $url . '</p>', "<div class='content-youtube'><iframe class='content-twitter' srcdoc='" . $twitter_html . "' onload='resizeIframe(this)'></iframe></div>", $total_content);
        }
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
}


// JSON으로 echo 처리하기
echo json_encode(array("list" => $list));

// DB 처리 종료
$stmt->close();
$conn->close();
?>