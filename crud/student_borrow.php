<?php
require_once '../db_con.php';
session_start();

// --- Session Validation ---
if (empty($_SESSION['username'])) {
    $_SESSION['msg'] = 'Please login first';
    header('location: ../log_in.php');
    exit;
}

if ($_SESSION['Identification'] != 'Student') {
    $_SESSION['msg'] = 'Please login first.';
    header('location: ../log_in.php');
    exit;
}

// --- Get Logged-in Student ID ---
$stmt = $connection->prepare("SELECT id FROM account_tbl WHERE username = :username");
$stmt->execute(['username' => $_SESSION['username']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$student_id = $student['id'] ?? 0;

// --- Borrow a Book ---
if (isset($_POST['borrow'])) {
    $book_id = $_POST['book_id'];
    $borrow_date = date('Y-m-d');
    $date_return = date('Y-m-d', strtotime('+7 days'));
    $is_returned = 0;

    try {
        $stmt = $connection->prepare("INSERT INTO borrow_books (book_id, user_id, borrow_date, date_return, is_returned)
                                      VALUES (:book_id, :user_id, :borrow_date, :date_return, :is_returned)");
        $stmt->execute([
            'book_id' => $book_id,
            'user_id' => $student_id,
            'borrow_date' => $borrow_date,
            'date_return' => $date_return,
            'is_returned' => $is_returned
        ]);

        $connection->prepare("UPDATE book_tbl SET availability = 0 WHERE id = :id")
            ->execute(['id' => $book_id]);

        
    } catch (Exception $e) {
        echo "<script>alert('❌ Error borrowing book: " . $e->getMessage() . "');</script>";
    }
}

// --- RETURN BOOK ---
if (isset($_POST['return'])) {
    $borrow_id = $_POST['borrow_id'];

    try {
        // Get book_id of borrowed record
        $stmt = $connection->prepare("SELECT book_id FROM borrow_books WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $borrow_id, 'uid' => $student_id]);
        $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($borrow) {
            // Mark as returned
            $connection->prepare("UPDATE borrow_books SET is_returned = 1 WHERE id = :id")
                ->execute(['id' => $borrow_id]);

            // Make book available again
            $connection->prepare("UPDATE book_tbl SET availability = 1 WHERE id = :id")
                ->execute(['id' => $borrow['book_id']]);

            echo "<script>Msg('✅ Book successfully returned!');</script>";
        } else {
            echo "<script>alert('⚠️ Invalid book record or unauthorized return.');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('❌ Error returning book: " . $e->getMessage() . "');</script>";
    }
}

// --- Fetch Available Books ---
$books = $connection->query("SELECT id, bookname, availability FROM book_tbl WHERE availability = 1 ORDER BY bookname ASC")->fetchAll();

// --- Fetch My Borrowed Books ---
$stmt = $connection->prepare("
    SELECT b.id AS borrow_id, b.book_id, bk.bookname, b.borrow_date, b.date_return, b.is_returned
    FROM borrow_books b
    JOIN book_tbl bk ON b.book_id = bk.id
    WHERE b.user_id = :uid
    ORDER BY b.id DESC
");
$stmt->execute(['uid' => $student_id]);
$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<body class="sb-nav-fixed">
<div id="layoutSidenav">
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="../index.php">Home</a>

   
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        
    </form>
    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
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
                <h1 class="mt-4">📘 Borrow a Book</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></li>
                </ol>

                <!-- Borrow Form -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-book me-1"></i> Borrow New Book
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Available Book:</label>
                                <select name="book_id" class="form-select" required>
                                    <option value="">-- Select Book --</option>
                                    <?php foreach ($books as $b): ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['bookname']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" name="borrow" class="btn btn-success w-100">Borrow Book</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- My Borrowed Books Table -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-book-reader me-1"></i> My Borrowed Books
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple" class="table table-bordered align-middle text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Book Name</th>
                                    <th>Borrow Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($borrows) > 0): ?>
                                    <?php foreach ($borrows as $row): ?>
                                    <tr>
                                        <td><?= $row['borrow_id'] ?></td>
                                        <td><?= htmlspecialchars($row['bookname']) ?></td>
                                        <td><?= htmlspecialchars($row['borrow_date']) ?></td>
                                        <td><?= htmlspecialchars($row['date_return']) ?></td>
                                        <td>
                                            <?php if ($row['is_returned']): ?>
                                                <span class="text-success fw-bold">Returned</span>
                                            <?php elseif ($row['date_return'] < date('Y-m-d')): ?>
                                                <span class="text-danger fw-bold">Overdue</span>
                                            <?php else: ?>
                                                <span class="text-warning fw-bold">Borrowed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$row['is_returned']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="borrow_id" value="<?= $row['borrow_id'] ?>">
                                                    <button type="submit" name="return" class="btn btn-sm btn-primary" onclick="return confirm('Return this book?')">
                                                        Return Book
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled>Returned</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No borrowed books yet 📖</td></tr>
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
