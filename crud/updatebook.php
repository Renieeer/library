<?php
require_once '../db_con.php';
session_start();

// ✅ LOGIN CHECK
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

// ✅ GET BOOK ID (from GET or POST)
$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) {
    echo "<script>alert('No book selected!'); window.location='history.php';</script>";
    exit;
}

// ✅ FETCH BOOK DATA
$stmt = $connection->prepare("SELECT * FROM book_tbl WHERE id = :id");
$stmt->execute(['id' => $id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo "<script>alert('Book not found!'); window.location='history.php';</script>";
    exit;
}

// ✅ FETCH SHELVES
$shelfStmt = $connection->query("SELECT * FROM shelves_tbl ORDER BY id ASC");
$shelves = $shelfStmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ HANDLE UPDATE
if (isset($_POST['update'])) {
    // safely get values only after submission
    $no = trim($_POST['no'] ?? '');
    $bookname = trim($_POST['bookname'] ?? '');
    $shelf_no = trim($_POST['shelf_no'] ?? '');
    $availability = trim($_POST['availability'] ?? '');

    
        try {
            $stmt = $connection->prepare("
                UPDATE book_tbl 
                SET no = :no, 
                    bookname = :book, 
                    shelf_no = :shelf, 
                    availability = :avail 
                WHERE id = :id
            ");
            $stmt->execute([
                'no' => $no,
                'book' => $bookname,
                'shelf' => $shelf_no,
                'avail' => $availability,
                'id' => $id
            ]);

            echo "<script> window.location='history.php';</script>";
            exit;
        } catch (PDOException $e) {
            echo "Error updating book: " . $e->getMessage();
        }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Book</title>
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
    <h2>Edit Book</h2>

    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">

        <div class="mb-3">
            <label class="form-label">Book Number</label>
            <input 
                type="number" 
                name="no" 
                class="form-control" 
                value="<?= htmlspecialchars($book['no']) ?>" 
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Book Name</label>
            <input 
                type="text" 
                name="bookname" 
                class="form-control" 
                value="<?= htmlspecialchars($book['bookname']) ?>" 
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Shelf</label>
            <select name="shelf_no" class="form-select" required>
                <option value="">-- Select Shelf --</option>
                <?php foreach ($shelves as $s): ?>
                    <option value="<?= $s['id'] ?>" 
                        <?= $book['shelf_no'] == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['shelf_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Availability</label>
            <select name="availability" class="form-select" required>
                <option value="">-- Select Status --</option>
                <option value="1" <?= $book['availability'] == 1 ? 'selected' : '' ?>>Available</option>
                <option value="0" <?= $book['availability'] == 0 ? 'selected' : '' ?>>Not Available</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <a href="history.php" class="btn btn-secondary">← Back</a>
            <button type="submit" name="update" class="btn btn-primary">Update Book</button>
        </div>
    </form>
</div>

</body>
</html>
