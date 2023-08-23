<?php

include_once '../cors.php';
include_once '../dbconn.php';

$user_id = $_POST['id'];
$userArr = [];

$stmt = $conn->prepare("SELECT * FROM app_users WHERE id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    unset($row['password']); // password 필드 제거
    $user = $row;
}

echo json_encode([
    'message' => '마이페이지 정보를 불러왔습니다.',
    'success' => true,
    'id'      => $user['id'],
    'name'    => $user['name'],
    'profile' => $user['profile'],
    'email'   => $user['email']   
]);

?>