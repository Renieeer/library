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
    $shelf_name = $_POST['shelf_name'];

    $stmt = $connection->prepare("INSERT INTO shelves_tbl (no, shelf_name) VALUES (:no, :shelf_name)");
    $stmt->execute(['no' => $no, 'shelf_name' => $shelf_name]);

    echo "<script>alert('Shelf added successfully!');</script>";
}

// --- READ ---
$stmt = $connection->query("SELECT * FROM shelves_tbl ORDER BY id ASC");
$shelves = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- DELETE ---
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $connection->prepare("DELETE FROM shelves_tbl WHERE id=:id");
    $stmt->execute(['id' => $id]);
    echo "<script>alert('Shelf deleted successfully!');</script>";
}
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
                    <h1 class="mt-4">Shelves Management</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Shelves</li>
                    </ol>

                    <!-- Add Shelf Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-plus me-1"></i>
                            Add New Shelf
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <div class="col-md-3">
                                    <label>No:</label>
                                    <input type="number" name="no" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Shelf Name:</label>
                                    <input type="text" name="shelf_name" class="form-control" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" name="add" class="btn btn-success w-100">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Shelves Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-layer-group me-1"></i>
                            Shelf List
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>No</th>
                                        <th>Shelf Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shelves as $s): ?>
                                    <tr>
                                        <td><?= $s['id'] ?></td>
                                        <td><?= htmlspecialchars($s['no']) ?></td>
                                        <td><?= htmlspecialchars($s['shelf_name']) ?></td>
                                        <td>
                                            <!-- Edit Button -->
                                            <form method="GET" action="upadate_Shelves.php" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">Edit</button>
                                            </form>

                                            <!-- Delete Button -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this shelf?')">Delete</button>
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
