<?php
include_once '../cors.php';
include_once '../dbconn.php';

class LikeCheck {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function postRead($post_id){
        
    }

}


$mode = $_POST['mode'] ?? null;
$post_id = $_POST['post_id'] ?? null;
$comment_id = $_POST['commentid'] ?? null;

$checker = new LikeCheck($conn);

if ($mode == 'post_read') {
    $checker->postRead($conn, $post_id);
}