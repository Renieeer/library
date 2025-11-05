<?php
require_once '../db_con.php';

// When the frontend calls ?api=true → return JSON only
if (isset($_GET['api'])) {
    header('Content-Type: application/json');

    // --- 1️⃣ USERS BY ROLE ---
    $user_sql = "SELECT Identification, COUNT(*) AS count FROM account_tbl GROUP BY Identification";
    $user_stmt = $connection->query($user_sql);
    $user_data = [];
    while ($row = $user_stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_data[$row['Identification']] = (int)$row['count'];
    }

    // --- 2️⃣ BOOKS PER SHELF ---
    $book_sql = "
        SELECT s.shelf_name, COUNT(b.id) AS count
        FROM shelves_tbl s
        LEFT JOIN book_tbl b ON b.shelf_no = s.id
        GROUP BY s.shelf_name
    ";
    $book_stmt = $connection->query($book_sql);
    $book_data = [];
    while ($row = $book_stmt->fetch(PDO::FETCH_ASSOC)) {
        $book_data[$row['shelf_name']] = (int)$row['count'];
    }

    // --- 3️⃣ BOOK AVAILABILITY ---
    $avail_sql = "SELECT availability, COUNT(*) AS count FROM book_tbl GROUP BY availability";
    $avail_stmt = $connection->query($avail_sql);
    $avail_data = ["Available" => 0, "Unavailable" => 0];
    while ($row = $avail_stmt->fetch(PDO::FETCH_ASSOC)) {
        $avail_data[$row['availability'] == 1 ? "Available" : "Unavailable"] = (int)$row['count'];
    }

    // --- 4️⃣ OVERDUE FINES TREND (LAST 6 MONTHS) ---
    $overdue_trend_sql = "
        SELECT 
            DATE_FORMAT(MIN(f.dy_start), '%b %Y') AS month_label,
            SUM(o.total_amount) AS total
        FROM overdues_tbl o
        LEFT JOIN fines_tbl f ON o.fine_id = f.id
        WHERE f.dy_start >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY YEAR(f.dy_start), MONTH(f.dy_start)
        ORDER BY YEAR(f.dy_start), MONTH(f.dy_start)
    ";
    $overdue_stmt = $connection->query($overdue_trend_sql);
    $overdue_labels = [];
    $overdue_totals = [];
    while ($row = $overdue_stmt->fetch(PDO::FETCH_ASSOC)) {
        $overdue_labels[] = $row['month_label'];
        $overdue_totals[] = (float)$row['total'];
    }

    // --- COMBINE ALL DATA ---
    echo json_encode([
        "users" => $user_data,
        "books" => $book_data,
        "availability" => $avail_data,
        "overdue" => [
            "labels" => $overdue_labels,
            "totals" => $overdue_totals
        ]
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>📊 Library System Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: Poppins, sans-serif; background: #f8f9fa; text-align: center; }
    canvas { max-width: 600px; margin: 30px auto; display: block; }
  </style>
</head>
<body>
  <h1>📈 Library System Dashboard</h1>

  <canvas id="userChart"></canvas>
  <canvas id="bookChart"></canvas>
  <canvas id="availabilityChart"></canvas>
  <canvas id="overdueChart"></canvas>

  <script>
    async function loadData() {
      const response = await fetch("?api=true");
      const data = await response.json();

      // 1️⃣ User Roles
      new Chart(document.getElementById('userChart'), {
        type: 'bar',
        data: {
          labels: Object.keys(data.users),
          datasets: [{
            label: 'Number of Users',
            data: Object.values(data.users),
            backgroundColor: ['#ff6384','#36a2eb','#ffcd56']
          }]
        },
        options: { plugins: { title: { display: true, text: 'User Roles Distribution' } } }
      });

      // 2️⃣ Books per Shelf
      new Chart(document.getElementById('bookChart'), {
        type: 'bar',
        data: {
          labels: Object.keys(data.books),
          datasets: [{
            label: 'Books Count',
            data: Object.values(data.books),
            backgroundColor: '#4bc0c0'
          }]
        },
        options: { plugins: { title: { display: true, text: 'Books per Shelf' } } }
      });

      // 3️⃣ Book Availability
      new Chart(document.getElementById('availabilityChart'), {
        type: 'pie',
        data: {
          labels: Object.keys(data.availability),
          datasets: [{
            data: Object.values(data.availability),
            backgroundColor: ['#36a2eb', '#ff9f40']
          }]
        },
        options: { plugins: { title: { display: true, text: 'Book Availability Status' } } }
      });

      // 4️⃣ Overdue Fines Trend
      new Chart(document.getElementById('overdueChart'), {
        type: 'line',
        data: {
          labels: data.overdue.labels.length > 0 ? data.overdue.labels : ['No Data'],
          datasets: [{
            label: 'Total Fines (₱)',
            data: data.overdue.totals.length > 0 ? data.overdue.totals : [0],
            borderColor: '#ff6384',
            backgroundColor: 'rgba(255,99,132,0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 5,
            pointHoverRadius: 7
          }]
        },
        options: {
          plugins: { title: { display: true, text: 'Overdue Fines (Last 6 Months)' } },
          scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Amount (₱)' } },
            x: { title: { display: true, text: 'Month' } }
          }
        }
      });
    }

    loadData();
  </script>
</body>
</html>
