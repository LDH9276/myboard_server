<?php

// CORS 허용
include_once 'cors.php';
$header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

//JWT 토큰 불러오기
include_once 'JWT.php';

//DB 연결
include_once 'dbconn.php';

// JWT 객체 생성
$jwt = new JWT();
$access_secret_key = $jwt->getAccessSecretKey();
$refresh_secret_key = $jwt->getRefreshSecretKey();
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$refreshHeader = $_COOKIE['refresh_token'] ?? null ;

// 액세스 토큰 가져오기
$token = substr($authHeader, strpos($authHeader, ' ') + 1);

// 액세스 토큰 해석
$accessResult = $jwt->decodeAccessToken($token);

// 조건 : 액세스 토큰이 유효하다면
if (is_array($accessResult)) {
  // 액세스 토큰이 유효하면 전달
  echo json_encode([
    'message'           => '토큰이 유효합니다.',
    'success'           => true,
    'user_id'           => $accessResult['id'],
    'user_name'         => $accessResult['name'],
    'user_info'         => $accessResult['user_info'],
    'user_profile_name' => $accessResult['user_profile_name'],
    'user_profile_ext'  => $accessResult['user_profile_ext'],
    'access_token'      => $token,
    'refresh_token'     => $refreshHeader
  ]);
} 

else {
  // 액세스 토큰이 유효하지 않다면
  // 리프레시 토큰과 액세스 토큰이 일치하는지 확인해본다

  // 리프레시 토큰 해석
  $refreshResult = $jwt->decodeRefreshToken($refreshHeader);
  
  // 검증 실패시
  if (!is_array($refreshResult)) {
    echo json_encode([
      'success' => false,
      'message' => '리프레시 토큰이 유효하지 않습니다.'
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
    $userPrifile = explode('.', $row2['profile_img']);

    $newAccessToken = [
      'id'                => $row2['id'],
      'name'              => $row2['name'],
      'user_info'         => $row2['user_info'],
      'user_profile_name' => $userPrifile[0],
      'user_profile_ext'  => $userPrifile[1],
      'exp'       => time() + (60 * 60) // 1시간 유지시간
    ];

    // 리프레시 토큰의 유효기간이 6시간 이하일 경우
    if($refreshResult['exp'] - time() < 60 * 60 * 6) {
      $newAccessToken = [
        'id'                => $row2['id'],
        'name'              => $row2['name'],
        'user_info'         => $row2['user_info'],
        'user_profile_name' => $userPrifile[0],
        'user_profile_ext'  => $userPrifile[1],
        'exp'       => time() + (60 * 60) // 1시간 유지시간
      ];

      $new_access_token = $jwt->issueAccessToken($newAccessToken);

      $newRefreshToken = [
        'id'        => $row2['id'],
        'exp'       => time() + (60 * 60 * 24) // 1일 유지시간
      ];

      $new_refresh_token = $jwt->issueRefreshToken($newRefreshToken);

      $refreshExp = date('Y-m-d H:i:s', time() + (60 * 60 * 24 * 7));

      $stmt = $conn->prepare("UPDATE app_token SET access_token = ?, refresh_token = ?, expire_refresh_token =? WHERE refresh_token = ?");
      $stmt->bind_param("ssss", $new_access_token, $new_refresh_token, $refreshExp, $refreshHeader);
      $stmt->execute();

      echo json_encode([
        'message'           => '두 토큰이 갱신되었습니다.',
        'success'           => true,
        'user_id'           => $row2['id'],
        'user_name'         => $row2['name'],
        'user_profile_name' => $userPrifile[0],
        'user_profile_ext'  => $userPrifile[1],
        'user_info'         => $row2['user_info'],
        'access_token'      => $new_access_token,
        'refresh_token'     => $refreshHeader
      ]);
    } else {

      $new_access_token = $jwt->issueAccessToken($newAccessToken);

      echo json_encode([
        'message'       => '액세스 토큰이 갱신되었습니다.',
        'success'           => true,
        'user_id'           => $row2['id'],
        'user_name'         => $row2['name'],
        'user_profile_name' => $userPrifile[0],
        'user_profile_ext'  => $userPrifile[1],
        'user_info'         => $row2['user_info'],
        'access_token'      => $new_access_token,
        'refresh_token'     => $refreshHeader
      ]);

      $stmt = $conn->prepare("UPDATE app_token SET access_token = ? WHERE refresh_token = ?");
      $stmt->bind_param("ss", $new_access_token, $refreshHeader);
      $stmt->execute();
    }
  }
}
?>