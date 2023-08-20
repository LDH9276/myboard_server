<?php

// CORS 허용
include 'cors.php';
$header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

//JWT 토큰 불러오기
include_once 'jwt.php';

//DB 연결
include_once 'dbconn.php';

// JWT 객체 생성
$jwt = new JWT();
$access_secret_key = $jwt->getAccessSecretKey();
$refresh_secret_key = $jwt->getRefreshSecretKey();
$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$refreshHeader = $_SERVER['HTTP_REFRESH'];

// 액세스 토큰 가져오기
$token = substr($authHeader, strpos($authHeader, ' ') + 1);

// 액세스 토큰 해석
$accessResult = $jwt->decodeAccessToken($token);

// 조건 : 액세스 토큰이 유효하다면
if (is_array($accessResult)) {
  // 액세스 토큰이 유효하면 전달
  echo json_encode([
    'message'       => '토큰이 유효합니다.',
    'success'       => true,
    'user_id'       => $result['id'],
    'user_name'     => $result['name'],
    'access_token'  => $token,
    'refresh_token' => $refreshHeader
  ]);
} 

else {
  // 액세스 토큰이 유효하지 않다면
  // 리프레시 토큰과 액세스 토큰이 일치하는지 확인해본다

  // 리프레시 토큰 해석
  $refreshResult = $jwt->decodeRefreshToken($refreshHeader);
  
  // 검증 실패시
  if (is_array(!$refreshResult)){
    echo json_encode([
      'success' => false,
      'message' => '토큰이 유효하지 않습니다.'
    ]);
    die;
  }

  $stmt = $conn->prepare("SELECT * FROM app_token WHERE refresh_token = ? and access_token = ?");
  $stmt->bind_param("ss", $refreshHeader, $token);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();

  if($result->num_rows == 0) {
    // 리프레시 토큰이 유효하지 않다면
    echo json_encode([
      'success' => false,
      'message' => '토큰이 유효하지 않습니다.'
    ]);
  } else {

    $stmt = $conn->prepare("SELECT * FROM app_users WHERE id = ?");
    $stmt->bind_param("s", $row['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row2 = $result->fetch_assoc();

    $newAccessToken = [
      'id'        => $row2['id'],
      'name'      => $row2['name'],
      'user_info' => $row2['user_info'],
      'exp'       => time() + (60 * 1) // 테스트, 1분
    ];

    $new_access_token = $jwt->issueAccessToken($newAccessToken);

    echo json_encode([
      'message'       => '토큰이 갱신되었습니다.',
      'success'       => true,
      'user_id'       => $row2['id'],
      'user_name'     => $row2['name'],
      'user_info'     => $row2['user_info'],
      'access_token'  => $new_access_token,
      'refresh_token' => $refreshHeader
    ]);

    $stmt = $conn->prepare("UPDATE app_token SET access_token = ? WHERE refresh_token = ?");
    $stmt->bind_param("ss", $new_access_token, $refreshHeader);
    $stmt->execute();
  }


  }

?>