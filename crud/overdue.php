<?php
require_once '../db_con.php';
session_start();

// --- Session Validation ---
if (empty($_SESSION['username'])) {
    $_SESSION['msg'] = 'Please login first';
    header('location: log_in.php');
    exit;
}

if ($_SESSION['Identification'] != 'Student') {
    header('location: log_in.php');
    exit;
}

// --- Get Active Fine Rate ---
$fineRate = 0;
try {
    $stmt = $connection->query("SELECT amount FROM fines_tbl WHERE is_active = 1 LIMIT 1");
    $fineRate = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) {
    $fineRate = 0;
}


$stmt = $connection->prepare("SELECT id FROM account_tbl WHERE username = :username");
$stmt->execute(['username' => $_SESSION['username']]);
$student_id = $stmt->fetchColumn();


if (isset($_POST['pay_fine_id'])) {
    $fineId = $_POST['pay_fine_id'];

    $stmt = $connection->prepare("UPDATE overdues_tbl SET is_paid = 1 WHERE id = :fid");
    $stmt->execute(['fid' => $fineId]);

    $_SESSION['msg'] = "Fine marked as paid successfully!";
    header("Location: overdue.php");
    exit;
}

// --- Fetch Overdue Borrow Records ---
$stmt = $connection->prepare("
    SELECT b.id AS borrow_id, b.user_id, bk.id AS book_id, bk.bookname, b.borrow_date, b.date_return, b.is_returned
    FROM borrow_books b
    JOIN book_tbl bk ON b.book_id = bk.id
    WHERE b.is_returned = 0 AND b.date_return < CURDATE() AND b.user_id = :uid
");
$stmt->execute(['uid' => $student_id]);
$overdues = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = new DateTime();

// --- Insert Overdue Records into overdues_tbl ---
foreach ($overdues as $row) {
    $returnDate = new DateTime($row['date_return']);
    $daysLate = $returnDate->diff($today)->days;
    $fine = $daysLate * $fineRate;

    $check = $connection->prepare("SELECT COUNT(*) FROM overdues_tbl WHERE borrowed_id = :borrow_id");
    $check->execute(['borrow_id' => $row['borrow_id']]);

    if ($check->fetchColumn() == 0) {
        $insert = $connection->prepare("
            INSERT INTO overdues_tbl (borrowed_id, fine_id, total_amount, is_paid)
            VALUES (:borrowed_id, 
                (SELECT id FROM fines_tbl WHERE is_active = 1 LIMIT 1),
                :total_amount,
                0
            )
        ");
        $insert->execute([
            'borrowed_id' => $row['borrow_id'],
            'total_amount' => $fine
        ]);
    }
}

// --- Fetch from overdues_tbl for display ---
$stmt = $connection->prepare("
    SELECT o.id, bk.bookname, b.borrow_date, b.date_return, o.total_amount, o.is_paid
    FROM overdues_tbl o
    JOIN borrow_books b ON o.borrowed_id = b.id
    JOIN book_tbl bk ON b.book_id = bk.id
    WHERE b.user_id = :uid
    ORDER BY o.id DESC
");
$stmt->execute(['uid' => $student_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="../index.php">PAGE</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<div id="layoutSidenav">
   <div id="layoutSidenav_content">
       <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link" href="../index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                       <div class="nav-item">
                            <a class="nav-link" href="student_borrow.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                                Borrow Books
                            </a>
                        </div>

                       <div class="nav-item">
                            <a class="nav-link" href="overdue.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                Fines
                            </a>
                        </div>
                        <div class="sb-sidenav-footer">
                            <div class="small">Logged in as:</div>
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Overdue Books & Fines</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard / Overdues</li>
                </ol>

                <!-- Fine Rate Summary -->
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-coins me-1"></i> Current Fine Rate
                    </div>
                    <div class="card-body">
                        <h5>Active Fine: 
                            <span class="text-success fw-bold">₱<?= number_format($fineRate, 2) ?> / day</span>
                        </h5>
                        <p class="text-muted">This rate is applied to all overdue books until returned.</p>
                    </div>
                </div>

                <!-- Overdue Table -->
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-exclamation-circle me-1"></i> Overdue Borrow Records
                    </div>
                    <div class="card-body">
                       

                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Book</th>
                                    <th>Borrow Date</th>
                                    <th>Return Date</th>
                                    <th>Fine (₱)</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($records): ?>
                                    <?php foreach ($records as $r): ?>
                                        <tr>
                                            <td><?= $r['id'] ?></td>
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
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to mark this fine as paid?');">
                                                        <input type="hidden" name="pay_fine_id" value="<?= $r['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-credit-card"></i> Pay
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>Paid</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">No overdue books 🎉</td></tr>
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
