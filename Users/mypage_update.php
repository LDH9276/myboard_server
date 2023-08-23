<?php

include_once '../cors.php';
include_once '../dbconn.php';

$user_id = $_POST['id'];
$user_name = $_POST['name'];
$user_profile = $_POST['profile'];
$user_email = $_POST['email'];

$user_id = mysqli_real_escape_string($conn, $user_id);
$user_name = mysqli_real_escape_string($conn, $user_name);
$user_profile = mysqli_real_escape_string($conn, $user_profile);
$user_email = mysqli_real_escape_string($conn, $user_email);

$stmt = $conn->prepare("UPDATE app_users SET name = ?, profile = ?, email = ? WHERE id = ?");
$stmt->bind_param("ssss", $user_name, $user_profile, $user_email, $user_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    echo json_encode([
        'message' => '회원정보가 수정되지 않았습니다.',
        'success' => false   
    ]);
    exit();
} else {
    echo json_encode([
        'message' => '회원정보가 수정되었습니다.',
        'success' => true   
    ]);
}

?>