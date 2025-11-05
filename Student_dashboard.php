<?php
require_once 'db_con.php';
session_start();

// --- Session Validation ---
if (empty($_SESSION['username'])) {
    $_SESSION['msg'] = 'Please login first';
    header('location: log_in.php');
    exit;
}

if ($_SESSION['Identification'] != 'Student') {
    $_SESSION['msg'] = 'Access denied.';
    header('location: log_in.php');
    exit;
}

// --- Get Logged-in Student ID ---
$student_username = $_SESSION['username'];

// Fetch the student ID
$stmt = $connection->prepare("SELECT id FROM account_tbl WHERE username = :username");
$stmt->execute(['username' => $student_username]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$student_id = $student['id'] ?? 0;

// --- Get Active Fine Rate ---
$fineRate = 0;
try {
    $stmt = $connection->query("SELECT amount FROM fines_tbl WHERE is_active = 1 LIMIT 1");
    $fineRate = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) {
    $fineRate = 0;
}

// --- Fetch Borrowed Books ---
$stmt = $connection->prepare("
    SELECT b.id AS borrow_id, bk.bookname, b.borrow_date, b.date_return, b.is_returned
    FROM borrow_books b
    JOIN book_tbl bk ON b.book_id = bk.id
    WHERE b.user_id = :uid
    ORDER BY b.id DESC
");
$stmt->execute(['uid' => $student_id]);
$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = new DateTime();
$totalFines = 0;
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'crud/includes/head.php'; ?>
<body class="sb-nav-fixed">
<div id="layoutSidenav">
 <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="index.php">PAGE</a>

   
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        
    </form>
    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

    <div id="layoutSidenav_content">
       <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link" href="../index.php.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                         <div class="nav-item">
                            <a class="nav-link" href="crud/student_borrow.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                                Borrow Books
                            </a>
                        </div>

                       <div class="nav-item">
                            <a class="nav-link" href="crud/overdue.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                Fines
                            </a>
                        </div>

                        <!-- <div class="nav-item">
                            <a class="nav-link" href="crud/paid_fines.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                                Paid fines
                            </a>
                        </div> -->


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
                <h1 class="mt-4">📚 Student Dashboard</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></li>
                </ol>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">Borrowed Books</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="text-white fw-bold fs-5">
                                    <?= count(array_filter($borrows, fn($b) => !$b['is_returned'])) ?>
                                </span>
                                <i class="fas fa-book fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card bg-danger text-white mb-4">
                            <div class="card-body">Overdue Books</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="text-white fw-bold fs-5">
                                    <?= count(array_filter($borrows, fn($b) => !$b['is_returned'] && $b['date_return'] < date('Y-m-d'))) ?>
                                </span>
                                <i class="fas fa-exclamation-circle fa-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card bg-warning text-dark mb-4">
                            <div class="card-body">Total Fines</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="fw-bold fs-5">
                                    ₱<?php
                                        foreach ($borrows as $b) {
                                            if (!$b['is_returned'] && $b['date_return'] < date('Y-m-d')) {
                                                $daysLate = (new DateTime($b['date_return']))->diff($today)->days;
                                                $totalFines += $daysLate * $fineRate;
                                            }
                                        }
                                        echo number_format($totalFines, 2);
                                    ?>
                                </span>
                                <i class="fas fa-coins fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Borrowed Books Table -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-book-reader me-1"></i> My Borrowed Books
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Book Name</th>
                                    <th>Borrow Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                    <th>Fine</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($borrows) > 0): ?>
                                    <?php foreach ($borrows as $row): 
                                        $isOverdue = (!$row['is_returned'] && $row['date_return'] < date('Y-m-d'));
                                        $daysLate = $isOverdue ? (new DateTime($row['date_return']))->diff($today)->days : 0;
                                        $fine = $daysLate * $fineRate;
                                    ?>
                                    <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                        <td><?= $row['borrow_id'] ?></td>
                                        <td><?= htmlspecialchars($row['bookname']) ?></td>
                                        <td><?= htmlspecialchars($row['borrow_date']) ?></td>
                                        <td><?= htmlspecialchars($row['date_return']) ?></td>
                                        <td>
                                            <?php if ($row['is_returned']): ?>
                                                <span class="text-success fw-bold">Returned</span>
                                            <?php elseif ($isOverdue): ?>
                                                <span class="text-danger fw-bold">Overdue</span>
                                            <?php else: ?>
                                                <span class="text-warning fw-bold">Borrowed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $isOverdue ? "₱" . number_format($fine, 2) : "₱0.00" ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-success">No borrowed books yet 🎉</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'crud/includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
