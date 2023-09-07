<?php

include_once 'cors.php';
include_once 'dbconn.php';
include_once 'JWT.php';

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
if(isset($row['progile_img']) && $row['progile_img'] !== null) {
  $userPrifile = explode('.', $row['progile_img']);
} else {
  $userPrifile = ['default', 'png'];
}

$dbPass = $row['password'] ?? '';

if(password_verify($password, $dbPass)) {

  // 토큰에 담을 데이터
  $newAccessToken = [
    'id'                => $row['id'],
    'name'              => $row['name'],
    'user_info'         => $row['user_info'],
    'user_profile_name' => $userPrifile[0],
    'user_profile_ext'  => $userPrifile[1],
    'exp'               => time() + (60 * 60)
  ];

  $newRefreshToken = [
    'id'        => $row['id'],
    'exp'       => time() + (60 * 60 * 24 * 7)
  ];


  // 액세스 토큰 발급
  $access_token = $jwt->issueAccessToken($newAccessToken);

  // 리프레시 토큰 발급
  $refresh_token = $jwt->issueRefreshToken($newRefreshToken);

  // 리프레시 만료일 7일 설정
  $refreshExp = date('Y-m-d H:i:s', time() + (60 * 60 * 24 * 7));

  setcookie("refresh_token", $refresh_token, [
    "expires" => time() + (60 * 60 * 24 * 7), // 7일 후 만료
    "path" => "/", // 모든 경로에서 사용 가능
    "httponly" => true, // JavaScript에서 접근 불가능하도록 설정
    "samesite" => "Strict" // 필요에 따라 "Lax"로 변경 가능
]);

    setcookie('test', 'test', [
        "expires" => time() + (60 * 60 * 24 * 7), // 7일 후 만료
        "path" => "/", // 모든 경로에서 사용 가능
        "httponly" => true, // JavaScript에서 접근 불가능하도록 설정
        "samesite" => "Strict" // 필요에 따라 "Lax"로 변경 가능
    ]);

  // DB로 ID, 액세스 토큰, 리프레시 토큰 저장
  $tokenInsert = $conn->prepare("INSERT INTO app_token (user_id, access_token, refresh_token, expire_refresh_token) VALUES (?, ?, ?, ?)");
  $tokenInsert->bind_param("ssss", $row['id'], $access_token, $refresh_token, $refreshExp);
  $tokenInsert->execute();

  if($tokenInsert->affected_rows === 0) {
    echo json_encode([
      'success' => false,
      'error' => $tokenInsert->error
    ]);
    exit;
  }

  // JSON으로 저장
  echo json_encode([
    'success' => true,
    'user_id' => $row['id'],
    'user_name' => $row['name'],
    'user_info' => $row['user_info'],
    'user_profile_name' => $userPrifile[0],
    'user_profile_ext' => $userPrifile[1],
    'access_token' => $access_token,
    'refresh_token' => $refresh_token
  ]);
} else {
  echo json_encode([
    'success' => false,
    'error' => '아이디와 비밀번호를 다시 확인해주세요'
  ]);
}

  // DB 연결 종료
  $stmt->close();
  $conn->close();
?>