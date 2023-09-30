<?php

// DB 연결
include_once '../cors.php';
include_once '../dbconn.php';

$search = $_POST['search'] ?? '';
$searchWildcard = '%' . implode('%', preg_split('//u', $search, -1, PREG_SPLIT_NO_EMPTY)) . '%';


$stmt = $conn->prepare("SELECT * FROM app_boardlist WHERE board_name LIKE ? ORDER BY board_subscriber DESC");
$stmt->bind_param("s", $searchWildcard);
$stmt->execute();
$result = $stmt->get_result();


$list = array();

while($row = mysqli_fetch_array($result)) {
    $id = $row['id'] ?? '';
    $board_name = $row['board_name'] ?? '';
    $board_thumb = $row['board_thumb'] ?? '';
    $board_subscriber = $row['board_subscriber'] ?? '';

    $list [] = array(
        "id" => $id,
        "board_name" => $board_name,
        "board_thumb" => $board_thumb,
        "board_subscriber" => $board_subscriber
    );
}

echo json_encode(array("list" => $list));
?>