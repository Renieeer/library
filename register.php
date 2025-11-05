<?php
require_once 'db_con.php';
$message = '';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);
    $identification = "Student"; // ✅ Automatically set as Student

    if (!empty($username) && !empty($password) && !empty($confirm)) {
        // 🟦 Verify if username is a valid Gmail
        if (!preg_match("/^[A-Za-z0-9._%+-]+@gmail\.com$/", $username)) {
            $message = "⚠️ Please use a valid Gmail address (e.g., user@gmail.com).";
        } elseif ($password !== $confirm) {
            $message = "❌ Passwords do not match.";
        } else {
            // Check if username already exists
            $stmt = $connection->prepare("SELECT * FROM account_tbl WHERE username = :username");
            $stmt->execute(['username' => $username]);

            if ($stmt->rowCount() > 0) {
                $message = "⚠️ Username already exists.";
            } else {
                // Hash password and insert user
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $connection->prepare("INSERT INTO account_tbl (username, password, Identification) VALUES (:user, :pass, :idtype)");

                try {
                    $stmt->execute([
                        'user' => $username,
                        'pass' => $hashed,
                        'idtype' => $identification
                    ]);
                    echo "<script> window.location='log_in.php';</script>";
                    exit;
                } catch (PDOException $e) {
                    $message = "❌ Error occurred while saving data.";
                }
            }
        }
    } else {
        $message = "⚠️ Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
</head>
<body>

<div class="card p-4" style="width: 400px;">
    <h3 class="text-center mb-4 text-primary">📝 Create Account</h3>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
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

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>

        <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
    </form>

    <div class="text-center mt-3">
        <small>Already have an account?</small>
        <a href="log_in.php">Login here</a>
         <a href="index.php" class="d-block mt-2">Back to Home</a>
    </div>
</div>

</body>
</html>

