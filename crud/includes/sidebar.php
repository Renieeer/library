<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <div class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#bookMenu" aria-expanded="false" aria-controls="bookMenu">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                        <span class="fw-bold">Book Management</span>
                    </a>

                    <div class="collapse ms-4" id="bookMenu">
                        <ul class="list-unstyled">
                        <li><a class="nav-link" href="shelves.php">Add Shelves</a></li>
                        <li><a class="nav-link" href="addbook.php">Add Books</a></li>
                        </ul>
                    </div>
                    </div>

                   
                <div class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#UserMenu" aria-expanded="false" aria-controls="UserMenu">
                       <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                        <span class="fw-bold">User Account</span>
                    </a>
                        <div class="collapse ms-4" id="UserMenu">
                                <ul class="list-unstyled">
                                <li><a class="nav-link" href="CreateAcc.php">Add Account</a></li>
                                </ul>
                        </div>
                </div>


                <div class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#borrowMenu" aria-expanded="false" aria-controls="borrowMenu">
                       <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                        <span class="fw-bold">Borrow Management</span>
                    </a>
                        <div class="collapse ms-4" id="borrowMenu">
                                <ul class="list-unstyled">
                                <li><a class="nav-link" href="history.php">History</a></li>
                                <li><a class="nav-link" href="borrow_book.php">Create</a></li>
                                </ul>
                        </div>
                </div>

                 <div class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#FineMenu" aria-expanded="false" aria-controls="FineMenu">
                       <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                        <span class="fw-bold">Fines Management</span>
                    </a>
                        <div class="collapse ms-4" id="FineMenu">
                                <ul class="list-unstyled">
                                <li><a class="nav-link" href="fines.php">Fines Field</a></li>
                                <li><a class="nav-link" href="admin_overdue.php">Over Due</a></li>
                                </ul>
                        </div>
                </div>


            </div>
        </div>
       <div class="sb-sidenav-footer">
    <div class="small">Logged in as:</div>
    <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
</div>

    </nav>
</div>