<?php

  // CORS 허용
  include 'cors.php';

  // 데이터베이스 연결
  include_once 'dbconn.php';

  // POST로 받아오기
  $id = $_POST['id'] ?? '';
  $password = $_POST['password'] ?? '';
  $name = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';
  
  // 특수문자 제거
  $id = mysqli_real_escape_string($conn, $id);
  $password = mysqli_real_escape_string($conn, $password);
  $name = mysqli_real_escape_string($conn, $name);
  $email = mysqli_real_escape_string($conn, $email);

  // 아이디 중복검사
  $sql = "SELECT * FROM app_users WHERE id = '$id'";
  $result = mysqli_query($conn, $sql);


  // 아이디가 중복되면 에러메시지를 보냄
  if(mysqli_num_rows($result) > 0) {
    echo json_encode([
      'idChk' => false,
    ]);
    exit;
  }

  // 비밀번호 암호화
  $password = password_hash($password, PASSWORD_DEFAULT);
  
  // 회원가입 절차 실행
  $stmt = $conn->prepare("INSERT INTO app_users (id, password, name, email) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $id, $password, $name, $email);
  $result = $stmt->execute();
  
  if($result) {
    // 회원가입 이후 JSON으로 성공 메시지를 보냄
    echo json_encode([
      'success' => true
    ]);
  } else {
    // 실패하면 JSON으로 실패 메시지를 보냄
    echo json_encode([
      'success' => false,
      'error' => 'Invalid username or password'
    ]);
  }
?>