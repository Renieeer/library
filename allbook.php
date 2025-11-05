<?php
require_once 'db_con.php';
session_start();

// Fetch all shelves
$shelves = $connection->query("SELECT * FROM shelves_tbl ORDER BY shelf_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all books with shelf info
$stmt = $connection->query("
    SELECT b.id, b.bookname, b.availability, s.shelf_name, s.id AS shelf_id
    FROM book_tbl b 
    LEFT JOIN shelves_tbl s ON b.shelf_no = s.id 
    ORDER BY b.bookname ASC
");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detect login
$isLoggedIn = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'crud/includes/head.php'; ?>

<body class="sb-nav-fixed"> 
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
</nav>
<div id="layoutSidenav">

    <!-- NAV SIDEBAR -->
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading"></div>
                    <h1><a  href="index.php">📚 Shelves</a></h1>
                    <a class="nav-link shelf-link active" href="#" data-shelf="all">
                        <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
                        All Books
                    </a>

                    <?php foreach ($shelves as $shelf): ?>
                        <a class="nav-link shelf-link" href="#" data-shelf="<?= $shelf['id'] ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                            <?= htmlspecialchars($shelf['shelf_name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">
        <main class="p-4">
            <h1 class="mt-2"><i class="fas fa-book-open me-2"></i>Library Collection</h1>
            <input type="text" id="bookSearch" class="form-control w-25 mb-3" placeholder="🔍 Search book...">

            <div id="bookGrid" class="row">
                <?php foreach ($books as $book): ?>
                    <div class="col-md-3 mb-4 book-card" data-shelf="<?= $book['shelf_id'] ?>">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-3x text-primary mb-3"></i>
                                <h5 class="card-title text-dark"><?= htmlspecialchars($book['bookname']) ?></h5>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-layer-group me-1"></i>
                                    <?= htmlspecialchars($book['shelf_name'] ?? 'Unknown') ?>
                                </p>
                                <p>
                                    <?php if ($book['availability'] == 1): ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Available</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="card-footer bg-light text-center">
                                <?php if ($book['availability'] == 1): ?>
                                    <?php if ($isLoggedIn): ?>
                                        <a href="crud/student_borrow.php?book_id=<?= $book['id'] ?>" class="btn btn-outline-success btn-sm">
                                            Borrow <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="log_in.php" class="btn btn-outline-primary btn-sm">Login to Borrow</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary btn-sm" disabled>Unavailable</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
        <?php include 'crud/includes/footer.php'; ?>
    </div>
</div>

<!-- JS Filter by Shelf -->
<script>
document.querySelectorAll('.shelf-link').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('.shelf-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');

        const shelfId = this.getAttribute('data-shelf');
        document.querySelectorAll('.book-card').forEach(card => {
            if (shelfId === 'all' || card.dataset.shelf === shelfId) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Search bar filter
document.getElementById("bookSearch").addEventListener("keyup", function() {
    const searchValue = this.value.toLowerCase();
    document.querySelectorAll(".book-card").forEach(book => {
        const title = book.querySelector(".card-title").textContent.toLowerCase();
        book.style.display = title.includes(searchValue) ? "block" : "none";
    });
});
</script>

</body>
</html>
