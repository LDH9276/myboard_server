<?php

include_once '../cors.php';
include_once '../dbconn.php';

$user_id = $_POST['id'];
$user_name = $_POST['name'];
$user_profile = $_POST['profile'];
$user_email = $_POST['email'];

$allowed_ext = array('jpg','jpeg','png','gif', 'webp', 'webm');


// 프로필 파일 받아오기
if(isset($_FILES['profile_img'])) {
$file_name  = $_FILES['profile_img']['name']; //파일명
$file_size  = $_FILES['profile_img']['size']; //파일크기
$file_tmp   = $_FILES['profile_img']['tmp_name']; //파일명
$file_type  = $_FILES['profile_img']['type']; //파일유형

$ext = explode('.',$file_name); 
$ext = strtolower(array_pop($ext));

$expensions = array("jpeg", "jpg", "png","gif", "webp", "webm"); //올라갈 파일 지정
//SWF나 EXE같은 악성코드 배포방지

if(in_array($ext, $expensions) === false){ //해당 확장자가 아니라면
    $errors[] = "올바른 확장자가 아닙니다.";
} //경고

if($file_size > 2097152) { //2MB이상 올라가면
    $errors[] = '파일 사이즈는 2MB 이상 초과할 수 없습니다.';
} //경고

if(empty($errors) == true) { //에러가 없다면
    move_uploaded_file($file_tmp, "./Profile/".$file_name); //경로에 저장
    $files = $file_name; // 변수에 파일명을 담는다
} else { //경고가 있다면
    print_r($errors); //경고출력
}
} else { // 만약 이미지 업로드가 아니라면
$files = null; //null로 반환한다.
}



$user_id = mysqli_real_escape_string($conn, $user_id);
$user_name = mysqli_real_escape_string($conn, $user_name);
$user_profile = mysqli_real_escape_string($conn, $user_profile);
$user_email = mysqli_real_escape_string($conn, $user_email);

$stmt = $conn->prepare("UPDATE app_users SET name = ?, profile = ?, email = ?, profile_img = ? WHERE id = ?");
$stmt->bind_param("sssss", $user_name, $user_profile, $user_email, $files, $user_id);
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