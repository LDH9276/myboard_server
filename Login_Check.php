<?php

include_once 'cors.php';
include_once 'dbconn.php';
include_once 'jwt.php';

// JWT 객체 생성
$jwt = new JWT();
$access_secret_key = $jwt->getAccessSecretKey();
$refresh_secret_key = $jwt->getRefreshSecretKey();

// 토큰 해석을 먼저 한다


// POST 요청으로 받은 데이터
// ID : 아이디
// password : 비밀번호
$id       =  $_POST['id'] ?? '';
$password =  $_POST['password'] ?? '';


// SQL 인젝션 방지
$id = mysqli_real_escape_string($conn, $id);
$password = mysqli_real_escape_string($conn, $password);

// DB에서 아이디 검색
$stmt = $conn->prepare("SELECT * FROM app_users WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = mysqli_fetch_array($result);
$dbPass = $row['password'] ?? '';

if(password_verify($password, $dbPass)) {

  // 토큰에 담을 데이터
  $newAccessToken = [
    'id'        => $row['id'],
    'name'      => $row['name'],
    'user_info' => $row['user_info'],
    'exp'       => time() + (60 * 60) // 1시간 유지시간
  ];

  $newRefreshToken = [
    'id'        => $row['id'],
    'exp'       => time() + (60 * 60 * 24) // 1일 유지시간
  ];


  // 액세스 토큰 발급
  $access_token = $jwt->issueAccessToken($newAccessToken);

  // 리프레시 토큰 발급
  $refresh_token = $jwt->issueRefreshToken($newRefreshToken);

  // DB로 ID, 액세스 토큰, 리프레시 토큰 저장
  $stmt = $conn->prepare("INSERT INTO app_token (user_id, access_token, refresh_token) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $row['id'], $access_token, $refresh_token);
  $stmt->execute();

  // JSON으로 저장
  echo json_encode([
    'success' => true,
    'user_id' => $row['id'],
    'user_name' => $row['name'],
    'user_info' => $row['user_info'],
    'access_token' => $access_token,
    'refresh_token' => $refresh_token
  ]);
} else {
  echo json_encode([
    'success' => false,
    'error' => 'Invalid username or password'
  ]);
}

  // DB 연결 종료
  $stmt->close();
  $conn->close();
?>