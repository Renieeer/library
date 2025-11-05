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

// --- UPDATE SHELF ---
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $no = $_POST['no'];
    $shelf_name = $_POST['shelf_name'];

    try {
        $stmt = $connection->prepare("UPDATE shelves_tbl SET no=:no, shelf_name=:shelf_name WHERE id=:id");
        $stmt->execute([
            'no' => $no,
            'shelf_name' => $shelf_name,
            'id' => $id
        ]);

        echo "<script> window.location='shelves.php';</script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

// --- FETCH SHELF ---
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $connection->prepare("SELECT * FROM shelves_tbl WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $shelf = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shelf) {
        die("<script>alert('Shelf not found!'); window.location='shelves.php';</script>");
    }
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
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Update Shelf</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Shelves</li>
                    </ol>

                   <div class="d-flex justify-content-center mt-5">
    <div class="card mb-4 col-md-8 shadow">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-edit me-1"></i>
            Edit Shelf Information
        </div>

        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="id" value="<?= htmlspecialchars($shelf['id']) ?>">

                <div class="col-md-6">
                    <label for="no" class="form-label">Shelf Number:</label>
                    <input type="number" name="no" class="form-control" 
                           value="<?= htmlspecialchars($shelf['no']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="shelf_name" class="form-label">Shelf Name:</label>
                    <input type="text" name="shelf_name" class="form-control" 
                           value="<?= htmlspecialchars($shelf['shelf_name']) ?>" required>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="submit" name="update" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="shelves.php" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

                    </div>
                </div>
              </div>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>

