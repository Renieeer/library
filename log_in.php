<?php
session_start();
require_once 'db_con.php';

$message = '';

if (!empty($_POST)) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 🟦 Prepare SQL — use AND, not comma
    $query = "SELECT * FROM account_tbl WHERE username = :username ";
    $stmt = $connection->prepare($query);

    // 🟩 Bind parameters correctly
    $stmt->execute(['username' => $username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
 

  if ($user) {

    // Check if username/email ends with "@gmail.com"
    if (!str_ends_with($user['username'], '@gmail.com')) {
        $_SESSION['msg'] = "⚠️ Only Gmail accounts are allowed.";
        header("Location: log_in.php");
        exit;
    }

    // Verify password
    if (password_verify($password, $user['password'])) {

        $_SESSION['username'] = $user['username'];
        $_SESSION['Identification'] = $user['Identification']; // match column name

        if ($_SESSION['username']) {

            switch (strtolower($_SESSION['Identification'])) {
                case 'admin':
                    header("Location: crud/dashboard.php");
                    break;
                case 'faculty':
                    header("Location: faculty_dashboard.php");
                    break;
                case 'student':
                    header("Location: Student_dashboard.php");
                    break;
                default:
                    $_SESSION['msg'] = "Unknown user role.";
                    header("Location: log_in.php");
                    break;
            }

        }
    } else {
        $_SESSION['msg'] = "❌ Wrong password.";
        header("Location: log_in.php");
        exit;
    }

} else {
    $_SESSION['msg'] = "❌ Username not found.";
    header("Location: log_in.php");
    exit;
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: "Poppins", sans-serif;
        }
        .card {
            width: 380px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            padding: 25px;
        }
        h3 {
            text-align: center;
            color: #0984e3;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #0984e3;
            border: none;
        }
        .btn-primary:hover {
            background-color: #74b9ff;
        }
    </style>
</head>
<body>

<div class="card">
    <h3>📚 Library Login</h3>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['msg']) ?></div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center mt-3">
        <small>Don’t have an account?</small>
        <a href="register.php">Register here</a>
        <a href="index.php" class="d-block mt-2">Back to Home</a>
    </div>
</div>

</body>
</html>