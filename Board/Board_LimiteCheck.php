<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

$stmt = $conn->prepare("SELECT id FROM app_boardlist order by id desc limit 1");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$last_id = $row['id'];


$post_limit = $conn->prepare("SELECT id FROM app_board order by id desc limit 1");
$post_limit->execute();
$post_result = $post_limit->get_result();
$post_row = $post_result->fetch_assoc();
$post_last_id = $post_row['id'];

$list = array();

$list[] = array(
    "last_board" => $last_id,
    "last_post" => $post_last_id
);

echo json_encode(array("list" => $list));
