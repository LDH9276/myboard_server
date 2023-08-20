<?php

include_once 'JWT_Verify.php';


?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>

<p>
<?php
// Check if access token exists and is valid
if (isset($_COOKIE['access_token'])) {
  
  // 토큰을 해석한 결과를 출력
  $data = $jwt->decodeAccessToken($_COOKIE['access_token']);

  // If token is valid, display user ID
  if ($data !== false) {
    echo "User ID: " . $data['id'];
  }
}


?>
</p>
</body>
</html>