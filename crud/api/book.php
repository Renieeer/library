<?php
require_once '../../db_con.php';
header('Content-Type: application/json');

$sql = "
    SELECT s.shelf_name, COUNT(b.id) AS count
    FROM shelves_tbl s
    LEFT JOIN book_tbl b ON b.shelf_no = s.id
    GROUP BY s.shelf_name
";
$stmt = $connection->query($sql);

$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[$row['shelf_name']] = (int)$row['count'];
}
echo json_encode($data);
