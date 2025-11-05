<?php
require_once '../db_con.php';
session_start();

// --- Session Validation ---
if (empty($_SESSION['username'])) {
    $_SESSION['msg'] = 'Please login first';
    header('location: ../log_in.php');
    exit;
}

if ($_SESSION['Identification'] != 'Admin') {
    $_SESSION['msg'] = 'Access denied.';
    header('location: ../log_in.php');
    exit;
}

// --- Handle Mark as Paid ---
if (isset($_POST['mark_paid_id'])) {
    $id = $_POST['mark_paid_id'];
    $stmt = $connection->prepare("UPDATE overdues_tbl SET is_paid = 1 WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $_SESSION['msg'] = "Fine marked as paid successfully!";
    header('location: admin_overdue.php');
    exit;
}

// --- Search Overdue Records ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "
    SELECT 
        o.id, 
        a.username AS borrower,
        bk.bookname,
        b.borrow_date,
        b.date_return,
        o.total_amount,
        o.is_paid
    FROM overdues_tbl o
    JOIN borrow_books b ON o.borrowed_id = b.id
    JOIN account_tbl a ON b.user_id = a.id
    JOIN book_tbl bk ON b.book_id = bk.id
";

if (!empty($search)) {
    $query .= " WHERE a.username LIKE :search OR bk.bookname LIKE :search ";
}

$query .= " ORDER BY o.id DESC";

$stmt = $connection->prepare($query);

if (!empty($search)) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}

$overdues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Get Active Fine Rate ---
$fineRate = $connection->query("SELECT amount FROM fines_tbl WHERE is_active = 1 LIMIT 1")->fetchColumn() ?: 0;

// --- Dashboard Stats ---
$totalBooks = $connection->query("SELECT COUNT(*) FROM book_tbl")->fetchColumn();
$totalUsers = $connection->query("SELECT COUNT(*) FROM account_tbl WHERE Identification = 'Student'")->fetchColumn();
$totalBorrowed = $connection->query("SELECT COUNT(*) FROM borrow_books")->fetchColumn();
$totalOverdue = $connection->query("SELECT COUNT(*) FROM overdues_tbl")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="../index.php">ADMIN PAGE</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<div id="layoutSidenav">
    <?php include 'includes/sidebar.php'; ?>

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

                <!-- Overdue Table -->
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-book me-1"></i> All Overdue Records
                    </div>
                    <div class="card-body">

                        <!-- 🔍 Search Bar -->
                        <form method="GET" class="d-flex mb-3">
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control me-2" 
                                placeholder="🔍 Search borrower or book..." 
                                value="<?= htmlspecialchars($search ?? '') ?>"
                            >
                            <button class="btn btn-primary" type="submit">Search</button>
                            <?php if (!empty($search)): ?>
                                <a href="admin_overdue.php" class="btn btn-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>

                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Borrower</th>
                                    <th>Book</th>
                                    <th>Borrow Date</th>
                                    <th>Return Date</th>
                                    <th>Fine (₱)</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($overdues): ?>
                                    <?php foreach ($overdues as $r): ?>
                                        <tr>
                                            <td><?= $r['id'] ?></td>
                                            <td><?= htmlspecialchars($r['borrower']) ?></td>
                                            <td><?= htmlspecialchars($r['bookname']) ?></td>
                                            <td><?= htmlspecialchars($r['borrow_date']) ?></td>
                                            <td><?= htmlspecialchars($r['date_return']) ?></td>
                                            <td class="text-danger fw-bold">₱<?= number_format($r['total_amount'], 2) ?></td>
                                            <td>
                                                <?php if ($r['is_paid']): ?>
                                                    <span class="badge bg-success">Paid</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Unpaid</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$r['is_paid']): ?>
                                                    <form method="POST" onsubmit="return confirm('Mark this fine as paid?');">
                                                        <input type="hidden" name="mark_paid_id" value="<?= $r['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check-circle"></i> Mark Paid
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>Paid</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center">No overdue fines 🎉</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
