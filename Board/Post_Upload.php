<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

$target_dir = "Upload/"; // 파일이 저장될 디렉토리
$target_file = $target_dir . basename($_FILES["file"]["name"]); // 파일 경로
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// 파일 업로드
if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
    echo json_encode(array("filename" => basename($_FILES["file"]["name"])));
} else {
    echo "Sorry, there was an error uploading your file.";
}
?>