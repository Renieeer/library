<?php
require_once '../../db_con.php';
header('Content-Type: application/json');

$user_sql = "SELECT Identification, COUNT(*) AS count FROM account_tbl GROUP BY Identification";
$stmt = $connection->query($user_sql);

$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[$row['Identification']] = (int)$row['count'];
}
echo json_encode($data);
