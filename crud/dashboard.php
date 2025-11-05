<?php 
require_once '../db_con.php';
session_start();

// --- Session Validation ---
if (empty($_SESSION['username'])) {
    $_SESSION['msg'] = 'Please login first';
    header('location: log_in.php');
    exit;
}

if ($_SESSION['Identification'] != 'Admin') {
    $_SESSION['msg'] = 'Please login First';
    header('location: log_in.php');
    exit;
}

// --- Dashboard Counters ---
$totalBooks = $connection->query("SELECT COUNT(*) FROM book_tbl")->fetchColumn();
$totalUsers = $connection->query("SELECT COUNT(*) FROM account_tbl WHERE Identification = 'Student'")->fetchColumn();
$totalBorrowed = $connection->query("SELECT COUNT(*) FROM borrow_books")->fetchColumn();
$totalOverdue = $connection->query("
    SELECT COUNT(*) 
    FROM borrow_books 
    WHERE is_returned = 0 
    AND date_return < CURDATE()
")->fetchColumn();


if (isset($_GET['api'])) {
    header('Content-Type: application/json');


    $user_sql = "SELECT Identification, COUNT(*) AS count FROM account_tbl GROUP BY Identification";
    $user_stmt = $connection->query($user_sql);
    $user_data = [];
    while ($row = $user_stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_data[$row['Identification']] = (int)$row['count'];
    }

    
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

    
    $avail_sql = "SELECT availability, COUNT(*) AS count FROM book_tbl GROUP BY availability";
    $avail_stmt = $connection->query($avail_sql);
    $avail_data = ["Available" => 0, "Unavailable" => 0];
    while ($row = $avail_stmt->fetch(PDO::FETCH_ASSOC)) {
        $avail_data[$row['availability'] == 1 ? "Available" : "Unavailable"] = (int)$row['count'];
    }

    
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
<?php include 'includes/head.php';?>

<body class="sb-nav-fixed">

<?php include 'includes/nav.php';?>

<div id="layoutSidenav">
    <?php include 'includes/sidebar.php';?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Dashboard</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard Overview</li>
                </ol>

                <!-- Dashboard Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">
                                <h5>Books Records</h5>
                                <h2><?= $totalBooks ?></h2>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="crud/book.php">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning text-white mb-4">
                            <div class="card-body">
                                <h5>Active Users</h5>
                                <h2><?= $totalUsers ?></h2>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="crud/createAcc.php">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">
                                <h5>Borrowed Books</h5>
                                <h2><?= $totalBorrowed ?></h2>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="crud/borrow.php">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-danger text-white mb-4">
                            <div class="card-body">
                                <h5>Overdue</h5>
                                <h2><?= $totalOverdue ?></h2>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="crud/borrow.php">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Books Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i> Books List
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Book Number</th>
                                    <th>Book Name</th>
                                    <th>Shelf No</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $books_stmt = $connection->query("SELECT * FROM book_tbl");
                                $bookname = $books_stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($bookname as $books): ?>
                                <tr>
                                    <td><?= $books['id'] ?></td>
                                    <td><?= $books['no'] ?></td>
                                    <td><?= $books['bookname'] ?></td>
                                    <td><?= $books['shelf_no'] ?></td>
                                    <td><?= ($books['availability'] == 1) ? '✅' : '❌' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 📊 Chart Section (2x2 Grid) -->
               
                    <h3 class="mb-3 text-center">📊 Library Analytics</h3>

                   <div class="row">
                        <!-- Left: 1 tall chart -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm p-3 h-100">
                                <h5 class="text-center">User Roles Distribution</h5>
                                <canvas id="userChart" style="height: 500px;"></canvas>
                            </div>
                        </div>

                        <!-- Right: 2 stacked charts -->
                        <div class="col-lg-6 d-flex flex-column">
                            <div class="card shadow-sm p-3 mb-4 flex-fill">
                                <h5 class="text-center">Books per Shelf</h5>
                                <canvas id="bookChart"></canvas>
                            </div>

                            <div class="card shadow-sm p-3 flex-fill">
                                <h5 class="text-center">Overdue Fines (Last 6 Months)</h5>
                                <canvas id="overdueChart"></canvas>
                            </div>
                        </div>
                    </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php';?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
async function fetchData(url) {
    const response = await fetch(url);
    return await response.json();
}

// 🧑‍🤝‍🧑 User Roles Chart
async function renderUserChart() {
    const users = await fetchData("api/user.php");
    new Chart(document.getElementById('userChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(users),
            datasets: [{
                data: Object.values(users),
                backgroundColor: ['#36a2eb', '#ff6384', '#ffcd56', '#4bc0c0']
            }]
        }
    });
}

// 📚 Books per Shelf Chart
async function renderBookChart() {
    const books = await fetchData("api/book.php");
    new Chart(document.getElementById('bookChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(books),
            datasets: [{
                data: Object.values(books),
                backgroundColor: '#4bc0c0'
            }]
        }
    });
}



// 💸 Overdue Fines Trend
async function renderOverdueChart() {
    const overdue = await fetchData("api/overdue_pie.php");
    new Chart(document.getElementById('overdueChart'), {
        type: 'bar',
        data: {
            labels: overdue.labels,
            datasets: [{
                label: 'Total Fines',
                data: overdue.totals,
                fill: false,
                borderColor: '#dc1641ff'
            }]
        }
    });
}

// 🚀 Run All Independently
renderUserChart();
renderBookChart();
renderOverdueChart();
</script>


</body>
</html>
