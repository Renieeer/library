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
    $_SESSION['msg'] = 'Bawal ka doon.';
    header('location: ../log_in.php');
    exit;
}

// --- CREATE (Borrow a Book) ---
if (isset($_POST['add'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_POST['user_id'];
    $borrow_date = $_POST['borrow_date'];
    $date_return = $_POST['date_return'];
    $is_returned = 0;

    $stmt = $connection->prepare("INSERT INTO borrow_books (book_id, user_id, borrow_date, date_return, is_returned)
                                  VALUES (:book_id, :user_id, :borrow_date, :date_return, :is_returned)");
    $stmt->execute([
        'book_id' => $book_id,
        'user_id' => $user_id,
        'borrow_date' => $borrow_date,
        'date_return' => $date_return,
        'is_returned' => $is_returned
    ]);

    $connection->prepare("UPDATE book_tbl SET availability = 0 WHERE id = :id")
        ->execute(['id' => $book_id]);

    
}

// --- RETURN NOW ---
if (isset($_POST['return_now'])) {
    $id = $_POST['id'];
    $book_id = $_POST['book_id'];

    $stmt = $connection->prepare("UPDATE borrow_books SET is_returned = 1 WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $connection->prepare("UPDATE book_tbl SET availability = 1 WHERE id = :id")
        ->execute(['id' => $book_id]);

 
}

// --- DELETE ---
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $connection->prepare("SELECT book_id FROM borrow_books WHERE id=:id");
    $stmt->execute(['id' => $id]);
    $book_id = $stmt->fetchColumn();

    $stmt = $connection->prepare("DELETE FROM borrow_books WHERE id=:id");
    $stmt->execute(['id' => $id]);

    if ($book_id) {
        $connection->prepare("UPDATE book_tbl SET availability = 1 WHERE id = :id")
            ->execute(['id' => $book_id]);
    }

    
}

// --- READ ---
$stmt = $connection->query("SELECT b.id, b.book_id, b.user_id, b.borrow_date, b.date_return, b.is_returned,
                                   a.username, bk.bookname
                            FROM borrow_books b
                            LEFT JOIN account_tbl a ON b.user_id = a.id
                            LEFT JOIN book_tbl bk ON b.book_id = bk.id
                            ORDER BY b.id ASC");
$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$users = $connection->query("SELECT id, username FROM account_tbl ORDER BY username ASC")->fetchAll();
$books = $connection->query("SELECT id, bookname, availability FROM book_tbl ORDER BY bookname ASC")->fetchAll();
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
                <h1 class="mt-4">Borrowed Books Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard / Borrow</li>
                </ol>

                
                <!-- Borrow Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-plus me-1"></i> Borrow a Book
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">User:</label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select User</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Book:</label>
                                <select name="book_id" class="form-select" required>
                                    <option value="">Select Available Book</option>
                                    <?php foreach ($books as $b): ?>
                                        <?php if ($b['availability'] == 1): ?>
                                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['bookname']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Borrow Date:</label>
                                <input type="date" name="borrow_date" class="form-control" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Return Date:</label>
                                <input type="date" name="date_return" class="form-control" required>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="add" class="btn btn-success w-100">Borrow</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Borrow Records Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-book me-1"></i> Borrow Records
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Book</th>
                                    <th>Borrow Date</th>
                                    <th>Return Date</th>
                                    <th>Returned?</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrows as $row): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['bookname']) ?></td>
                                    <td><?= $row['borrow_date'] ?></td>
                                    <td><?= $row['date_return'] ?></td>
                                    <td><?= ($row['is_returned'] == 1) ? "<span class='text-success fw-bold'>✅ Yes</span>" : "<span class='text-danger fw-bold'>❌ No</span>" ?></td>
                                   <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="book_id" value="<?= $row['book_id'] ?>">
                                                
                                                <?php if ($row['is_returned'] == 0): ?>
                                                    <button type="submit" 
                                                            name="return_now" 
                                                            class="btn btn-success btn-sm"
                                                            onclick="return confirm('⚠️ Confirm: Has the student physically returned this book?')">
                                                        Confirm Return
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>Returned</button>
                                                <?php endif; ?>
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
