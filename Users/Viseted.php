<?php

include_once '../cors.php';
include_once '../dbconn.php';

$user_id = $_POST['id'];

$user_id = mysqli_real_escape_string($conn, $user_id);

$stmt = $conn->prepare("SELECT visited_board FROM app_users WHERE id = ?");



?>