<?php
require_once '../../db_con.php';
header('Content-Type: application/json');

$sql = "
    SELECT 
        DATE_FORMAT(MIN(f.dy_start), '%b %Y') AS month_label,
        SUM(o.total_amount) AS total
    FROM overdues_tbl o
    LEFT JOIN fines_tbl f ON o.fine_id = f.id
    WHERE f.dy_start >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(f.dy_start), MONTH(f.dy_start)
    ORDER BY YEAR(f.dy_start), MONTH(f.dy_start)
";
$stmt = $connection->query($sql);

$labels = [];
$totals = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $labels[] = $row['month_label'];
    $totals[] = (float)$row['total'];
}
echo json_encode(["labels" => $labels, "totals" => $totals]);
