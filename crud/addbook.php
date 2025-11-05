<?php  
require_once '../db_con.php';
session_start();

// --- SESSION VALIDATION ---
if (empty($_SESSION['username'])) {
    $_SESSION['msg'] = 'Please login first';
    header('location: log_in.php');
    exit;
}
if ($_SESSION['Identification'] != 'Admin') {
    $_SESSION['msg'] = 'You are not allowed here.';
    header('location: log_in.php');
    exit;
}

// --- CREATE ---
if (isset($_POST['add'])) {
    $book_no = trim($_POST['book_no']);
    $bookname = trim($_POST['bookname']);
    $shelf_no = trim($_POST['shelf_no']);
    $availability = 1; // default available

    if (!empty($book_no) && !empty($bookname) && !empty($shelf_no)) {
        $stmt = $connection->prepare("
            INSERT INTO book_tbl (no, bookname, shelf_no, availability)
            VALUES (:no, :bookname, :shelf_no, :availability)
        ");
        $stmt->execute([
            'no' => $book_no,
            'bookname' => $bookname,
            'shelf_no' => $shelf_no,
            'availability' => $availability
        ]);
        echo "<script>window.location='addBook.php';</script>";
    } else {
        echo "<script>alert('⚠️ Please fill all fields.');</script>";
    }
}

// --- DELETE ---
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $connection->prepare("DELETE FROM book_tbl WHERE id=:id");
    $stmt->execute(['id' => $id]);
    echo "<script>alert('🗑️ Book deleted successfully!'); window.location='addBook.php';</script>";
    exit;
}

// --- READ ---
$stmt = $connection->query("
    SELECT b.*, s.shelf_name 
    FROM book_tbl b
    LEFT JOIN shelves_tbl s ON b.shelf_no = s.id
");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch Shelves for Dropdown ---
$stmt = $connection->query("SELECT * FROM shelves_tbl");
$shelves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>

<body class="sb-nav-fixed">
<?php include 'includes/nav.php'; ?>

<div id="layoutSidenav">
    <?php include 'includes/sidebar.php'; ?>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">

                <h1 class="mt-4">Book Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard / Books</li>
                </ol>

                <!-- Dashboard Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">Total Books</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="small text-white"><?= count($books) ?> Books</span>
                                <div class="small text-white"><i class="fas fa-book"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">Available Books</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="small text-white">
                                    <?= count(array_filter($books, fn($b) => $b['availability'] == 1)) ?>
                                </span>
                                <div class="small text-white"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card bg-danger text-white mb-4">
                            <div class="card-body">Unavailable Books</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="small text-white">
                                    <?= count(array_filter($books, fn($b) => $b['availability'] == 0)) ?>
                                </span>
                                <div class="small text-white"><i class="fas fa-times-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Book Form -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-1"></i> Add New Book
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Book No:</label>
                                <input type="number" name="book_no" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Book Name:</label>
                                <input type="text" name="bookname" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Shelf:</label>
                                <select name="shelf_no" class="form-select" required>
                                    <option value="">-- Select Shelf --</option>
                                    <?php foreach ($shelves as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['shelf_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="add" class="btn btn-success w-100">Add</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Book List Table -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-book me-1"></i> Book List
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple" class="table table-bordered table-hover text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Book No</th>
                                    <th>Book Name</th>
                                    <th>Shelf</th>
                                    <th>Availability</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $b): ?>
                                <tr>
                                    <td><?= $b['id'] ?></td>
                                    <td><?= htmlspecialchars($b['no']) ?></td>
                                    <td><?= htmlspecialchars($b['bookname']) ?></td>
                                    <td><?= htmlspecialchars($b['shelf_name'] ?? 'Unknown') ?></td>
                                    <td>
                                        <?php if ($b['availability'] == 1): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Unavailable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Edit Button -->
                                        <form method="GET" action="updateBook.php" style="display:inline;">
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
        <?php include 'includes/footer.php'; ?>
    </div>
</div>
</body>
</html>
