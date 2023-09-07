<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// POST로 받아오기
$id = $_POST['post_id'] ?? '';
$user_id = $_POST['user_id'] ?? '';
$id = (int)$id;

// 리스트 출력하기
$stmt = $conn->prepare("SELECT * FROM app_comment WHERE post_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// 리스트 반복처리 후 JSON 데이터로 변환
$list = array();

while ($row = mysqli_fetch_array($result)) {

    if ($row['is_deleted'] == 1) {
        $id = $row['id'] ?? '';
        $content = '삭제된 댓글입니다.';
        $writer = '';
        $reg_date = '';
        $commnet_parent = $row['comment_parent'] ?? null;
        $comment_depth = $row['comment_depth'] ?? '';
        $total_like = 0;
        $is_deleted = 1;
    } else {
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
                $total_content = str_replace($url, $youtube_html, $total_content);
            }
        }



        $id = $row['id'] ?? '';
        $writer = $row['writer'] ?? '';
        $reg_date = $row['reg_date'] ?? '';
        $commnet_parent = $row['comment_parent'] ?? null;
        $comment_depth = $row['comment_depth'] ?? '';
        $total_like = $row['total_like'] ?? 0;
        $is_deleted = 0;
    }

    // 해당 회원의 좋아요 여부 확인 (23.8.27추가)
    $stmt_like = $conn->prepare("SELECT * FROM app_comment_like WHERE comment_id = ? AND user_id = ? and is_delete = false");
    $stmt_like->bind_param("is", $id, $user_id);
    $stmt_like->execute();
    $result_like = $stmt_like->get_result();

    if ($result_like->num_rows == 0) {
        $like = false;
    } else {
        $like = true;
    }

    $list[] = array(
        'id' => $id,
        'content' => $total_content,
        'writer' => $writer,
        'reg_date' => $reg_date,
        'total_like' => $total_like,
        'comment_parent' => $commnet_parent,
        'comment_depth' => $comment_depth,
        'is_deleted' => $is_deleted,
        'like' => $like,
        'user_id' => $user_id ?? ''
    );
}

// JSON으로 echo 처리하기
echo json_encode(array("list" => $list));

// DB 처리 종료
$stmt->close();
$conn->close();
