<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
// Reset daily video counters for all users at midnight UTC
DB::q("UPDATE users SET videos_today = 0 WHERE last_video_date < CURDATE()");
echo date('[Y-m-d H:i:s]') . " Daily video counters reset.\n";
?>
