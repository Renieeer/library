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
    $_SESSION['msg'] = 'Bawal ka doon.';
    header('location: log_in.php');
    exit;
}

// --- CREATE ---
if (isset($_POST['add'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $identification = trim($_POST['identification']);

    if (!empty($username) && !empty($password) && !empty($identification)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("INSERT INTO account_tbl (username, password, Identification) VALUES (:user, :pass, :ident)");
        $stmt->execute([
            'user' => $username,
            'pass' => $hashed,
            'ident' => $identification
        ]);
        echo "<script>window.location='createAcc.php';</script>";
    } else {
        echo "<script>alert('⚠️ Please fill all fields.');</script>";
    }
}

// --- DELETE ---
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $connection->prepare("DELETE FROM account_tbl WHERE id=:id");
    $stmt->execute(['id' => $id]);
    echo "<script>('🗑️ Account deleted successfully!'); window.location='createAcc.php';</script>";
    exit;
}

// --- READ ---
$stmt = $connection->query("SELECT * FROM account_tbl");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

                <h1 class="mt-4">Account Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>

                <!-- Dashboard Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">Accounts Registered</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="small text-white"><?= count($accounts) ?> Accounts</span>
                                <div class="small text-white"><i class="fas fa-users"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">Admins</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="small text-white">
                                    <?= count(array_filter($accounts, fn($a) => $a['Identification'] == 'Admin')) ?>
                                </span>
                                <div class="small text-white"><i class="fas fa-user-shield"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning text-white mb-4">
                            <div class="card-body">Faculty</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="small text-white">
                                    <?= count(array_filter($accounts, fn($a) => $a['Identification'] == 'Faculty')) ?>
                                </span>
                                <div class="small text-white"><i class="fas fa-chalkboard-teacher"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-danger text-white mb-4">
                            <div class="card-body">Students</div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <span class="small text-white">
                                    <?= count(array_filter($accounts, fn($a) => $a['Identification'] == 'Student')) ?>
                                </span>
                                <div class="small text-white"><i class="fas fa-user-graduate"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Account Form -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-user-plus me-1"></i> Add New Account
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Username:</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Identification:</label>
                                <select name="identification" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    <option value="Student">Student</option>
                                    <option value="Faculty">Faculty</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" name="add" class="btn btn-success w-100">Add</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account List Table -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-users me-1"></i> Account List
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple" class="table table-bordered table-hover text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Password (Hashed)</th>
                                    <th>Identification</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td><?= $acc['id'] ?></td>
                                    <td><?= htmlspecialchars($acc['username']) ?></td>
                                    <td><small><?= htmlspecialchars($acc['password']) ?></small></td>
                                    <td>
                                        <?php 
                                            $role = htmlspecialchars($acc['Identification']);
                                            $badgeColor = $role == 'Admin' ? 'bg-danger' : ($role == 'Faculty' ? 'bg-warning text-dark' : 'bg-success');
                                        ?>
                                        <span class="badge <?= $badgeColor ?>"><?= $role ?></span>
                                    </td>
                                    <td>
                                        <!-- Update Button -->
                                        <form method="GET" action="updateAcc.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $acc['id'] ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">Edit</button>
                                        </form>
                                        <!-- Delete Button -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $acc['id'] ?>">
                                            <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this account?')">Delete</button>
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
