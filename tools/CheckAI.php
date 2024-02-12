<?php
/**
 * Get next auto-increment value for HIKES
 */
require "../php/global_boot.php";
$ai_state = <<<AI
SELECT AUTO_INCREMENT
FROM information_schema.tables
WHERE table_name = 'HIKES'
AND table_schema = DATABASE( );
AI;
$state = $pdo->query($ai_state)->fetch(PDO::FETCH_NUM);
$x =1;
echo "OK";
