<?php
require_once '../db_con.php';
session_start();

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

// ✅ 1️⃣ FETCH ACCOUNT DETAILS TO EDIT
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $connection->prepare("SELECT * FROM account_tbl WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        echo "<script>alert('Account not found!'); window.location='createAcc.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('No account selected!'); window.location='createAcc.php';</script>";
    exit;
}

// ✅ 2️⃣ HANDLE UPDATE SUBMISSION
if (isset($_POST['update'])) {
    $id = trim($_POST['id']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $identification = trim($_POST['identification']);

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("
            UPDATE account_tbl 
            SET username = :user, password = :pass, Identification = :idtype 
            WHERE id = :id
        ");
        $stmt->execute([
            'user' => $username,
            'pass' => $hashed,
            'idtype' => $identification,
            'id' => $id
        ]);
    } else {
        $stmt = $connection->prepare("
            UPDATE account_tbl 
            SET username = :user, Identification = :idtype 
            WHERE id = :id
        ");
        $stmt->execute([
            'user' => $username,
            'idtype' => $identification,
            'id' => $id
        ]);
    }

    echo "<script>; window.location='createAcc.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f8;
        }
        .container {
            max-width: 600px;
            margin-top: 60px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #273c75;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #273c75;
            border: none;
        }
        .btn-primary:hover {
            background-color: #192a56;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Account</h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($account['id']) ?>">

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input 
                type="text" 
                name="username" 
                class="form-control" 
                value="<?= htmlspecialchars($account['username']) ?>" 
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">New Password (optional)</label>
            <input 
                type="password" 
                name="password" 
                class="form-control" 
                placeholder="Leave blank to keep current"
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Identification</label>
            <select name="identification" class="form-select" required>
                <option value="">-- Select Role --</option>
                <option value="Student" <?= $account['Identification'] == 'Student' ? 'selected' : '' ?>>Student</option>
                <option value="Faculty" <?= $account['Identification'] == 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                <option value="Admin" <?= $account['Identification'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="createAcc.php" class="btn btn-secondary">← Back</a>
            <button type="submit" name="update" class="btn btn-primary">Update Account</button>
        </div>
    </form>
</div>

</body>
</html>
