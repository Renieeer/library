<?php 
    include '../db_con.php';
    session_start();

    // --- Session Validation ---
    if (empty($_SESSION['username'])) {
        $_SESSION['msg'] = 'Please login first';
        header('location: log_in.php');
        exit;
    }

    if ($_SESSION['Identification'] != 'Admin') {
        $_SESSION['msg'] = 'Bawal ka doon.';
        header('location: log_in.php');
        exit;
    }

    // --- CREATE ---
    if (isset($_POST['add'])) {
        $no = $_POST['no'];
        $bookname = $_POST['bookname'];
        $shelf_name = $_POST['shelf_name'];
        $availability = $_POST['availability'];

        $stmt = $connection->prepare("INSERT INTO book_tbl (no, bookname, shelf_name, availability) VALUES (:no, :book, :shelf_name, :avail)");
        $stmt->execute(['no' => $no, 'book' => $bookname, 'shelf_name' => $shelf_name, 'avail' => $availability]);
        echo "<script>alert('Book added successfully!');</script>";
    }

    // --- READ ---
  $stmt = $connection->query("
    SELECT b.id, b.no, b.bookname, b.availability, s.shelf_name
    FROM book_tbl b
    LEFT JOIN shelves_tbl s ON b.shelf_no = s.id
");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // --- DELETE ---
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $connection->prepare("DELETE FROM book_tbl WHERE id=:id");
        $stmt->execute(['id' => $id]);
        echo "<script>alert('Book deleted successfully!');</script>";
    }

    // --- Get Shelves for Dropdown ---
    $shelfStmt = $connection->query("SELECT * FROM shelves_tbl ORDER BY id ASC");
    $shelves = $shelfStmt->fetchAll(PDO::FETCH_ASSOC);


// for book statistics
$totalBooks = $connection->query("SELECT COUNT(*) FROM book_tbl")->fetchColumn();
$totalUsers = $connection->query("SELECT COUNT(*) FROM account_tbl WHERE Identification = 'Student'")->fetchColumn();
$totalBorrowed = $connection->query("SELECT COUNT(*) FROM borrow_books")->fetchColumn();
$totalOverdue = $connection->query("
    SELECT COUNT(*) 
    FROM borrow_books 
    WHERE is_returned = 0 
    AND date_return < CURDATE()
")->fetchColumn();

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
                        <h1 class="mt-4">DashBoard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                  <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">
                                <h5>Books Records</h5>
                                <h2><?= $totalBooks ?></h2>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="crud/book.php"></a>
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
                                <a class="small text-white stretched-link" href="crud/createAcc.php"></a>
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
                                <a class="small text-white stretched-link" href="crud/borrow.php"></a>
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
                                <a class="small text-white stretched-link" href="crud/borrow.php"></a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                        <!-- Book Table -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-book me-1"></i>
                                Book List
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>No</th>
                                            <th>Book Name</th>
                                            <th>Shelf Name</th>
                                            <th>Availability</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php foreach ($books as $b): ?>
                                        <tr>
                                            <td><?= $b['id'] ?></td>
                                            <td><?= $b['no'] ?></td>
                                            <td><?= htmlspecialchars($b['bookname']) ?></td>
                                            <td><?= htmlspecialchars($b['shelf_name']) ?></td>
                                            <td><?= ($b['availability'] == 1) ? "<span class='text-success fw-bold'>Yes</span>" : "<span class='text-danger fw-bold'>No</span>" ?></td>
                                            <td>
                                                <!-- Update Button -->
                                                <form method="GET" action="updatebook.php" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm">Edit</button>
                                                </form>

                                                <!-- Delete Button -->
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                                    <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>

                <?php include 'includes/footer.php';?>
            </div>
        </div>
    </body>
</html>
