<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

// 변수 수신
$writer = $_POST['writer'];

// 쿼리 작성
$stmt = $conn->prepare("SELECT * FROM app_users WHERE id = ?");
$stmt->bind_param("s", $writer);
$stmt->execute();
$result = $stmt->get_result();

// 결과 반환
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userProfile = explode('.', $row['profile_img']);

    $data = array(
        'id' => $row['id'],
        'name' => $row['name'],
        'profile_name' => $userProfile[0],
        'profile_ext' => $userProfile[1],
        'profile' => $row['profile']
    );
    echo json_encode($data);
} else {
    echo json_encode(array('result' => 'fail'));
}
