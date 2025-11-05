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
    $_SESSION['msg'] = 'Access denied.';
    header('location: log_in.php');
    exit;
}

// --- ADD FINE ---
if (isset($_POST['add'])) {
    $amount = $_POST['amount'];
    $dy_start = $_POST['dy_start'];
    $dy_end = $_POST['dy_end'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // deactivate all existing fines if new one is active
    if ($is_active == 1) {
        $connection->exec("UPDATE fines_tbl SET is_active = 0");
    }

    $stmt = $connection->prepare("INSERT INTO fines_tbl (amount, is_active, dy_start, dy_end) 
                                  VALUES (:amount, :is_active, :dy_start, :dy_end)");
    $stmt->execute([
        'amount' => $amount,
        'is_active' => $is_active,
        'dy_start' => $dy_start,
        'dy_end' => $dy_end
    ]);

    echo "<script>alert('✅ Fine added successfully!');</script>";
}

// --- UPDATE FINE ---
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $amount = $_POST['amount'];
    $dy_start = $_POST['dy_start'];
    $dy_end = $_POST['dy_end'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($is_active == 1) {
        $connection->exec("UPDATE fines_tbl SET is_active = 0");
    }

    $stmt = $connection->prepare("UPDATE fines_tbl 
        SET amount=:amount, is_active=:is_active, dy_start=:dy_start, dy_end=:dy_end WHERE id=:id");
    $stmt->execute([
        'amount' => $amount,
        'is_active' => $is_active,
        'dy_start' => $dy_start,
        'dy_end' => $dy_end,
        'id' => $id
    ]);

    echo "<script>alert('✅ Fine updated successfully!');</script>";
}

// --- READ FINES ---
$stmt = $connection->query("SELECT * FROM fines_tbl ORDER BY id DESC");
$fines = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <h1 class="mt-4">Fine Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard / Fines</li>
                </ol>

                <!-- ADD FINE FORM -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-plus me-1"></i> Set or Update Fines
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Fine Amount (₱):</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Start Date:</label>
                                <input type="date" name="dy_start" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date:</label>
                                <input type="date" name="dy_end" class="form-control">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1">
                                    <label class="form-check-label">Active?</label>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" name="add" class="btn btn-success w-100">Add</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- FINES TABLE -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-coins me-1"></i> Fines List
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple" class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Amount (₱)</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Active</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fines as $fine): ?>
                                <tr>
                                    <form method="POST">
                                        <td><?= $fine['id'] ?></td>
                                        <td><input type="number" name="amount" step="0.01" value="<?= $fine['amount'] ?>" class="form-control"></td>
                                        <td><input type="date" name="dy_start" value="<?= $fine['dy_start'] ?>" class="form-control"></td>
                                        <td><input type="date" name="dy_end" value="<?= $fine['dy_end'] ?>" class="form-control"></td>
                                        <td>
                                            <input type="checkbox" name="is_active" value="1" <?= ($fine['is_active']) ? 'checked' : '' ?>>
                                        </td>
                                        
                                    </form>
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
