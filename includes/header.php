<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME; ?>
    </title>
    <link rel="stylesheet" href="/public/css/style.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <h1><a href="/" style="color: white; text-decoration: none;">
                        <?php echo SITE_NAME; ?>
                    </a></h1>
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a href="/admin/index.php">Admin Dashboard</a></li>
                            <li><a href="/admin/rounds.php">Rounds</a></li>
                            <li><a href="/admin/users.php">Users</a></li>
                        <?php else: ?>
                            <li><a href="/member/index.php">Dashboard</a></li>
                            <li><a href="/member/tipping.php">My Tips</a></li>
                            <li><a href="/member/points.php">Points & Tally</a></li>
                        <?php endif; ?>
                        <li><a href="/logout.php">Logout (
                                <?php echo htmlspecialchars($_SESSION['username']); ?>)
                            </a></li>
                    <?php else: ?>
                        <li><a href="/index.php">Login</a></li>
                        <li><a href="/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">