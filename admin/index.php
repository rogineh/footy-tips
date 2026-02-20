<?php
$page_title = "Admin Dashboard";
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_admin();

// Get counts for dashboard
$pending_count = $conn->query("SELECT COUNT(*) FROM users WHERE is_approved = 0")->fetch_row()[0];
$round_count = $conn->query("SELECT COUNT(*) FROM rounds")->fetch_assoc(); // Simplified
$member_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'member' AND is_approved = 1")->fetch_row()[0];

?>

<div class="card">
    <h2>Admin Dashboard</h2>
    <p>Welcome, <strong>
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </strong>!</p>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 2rem;">
        <div class="card" style="margin-top: 0; text-align: center; background: #e3f2fd;">
            <h3 style="color: #1565c0;">
                <?php echo $pending_count; ?>
            </h3>
            <p>Pending Approvals</p>
            <a href="users.php" class="btn btn-accent" style="margin-top: 10px; font-size: 0.8rem;">Review</a>
        </div>
        <div class="card" style="margin-top: 0; text-align: center; background: #e8f5e9;">
            <h3 style="color: #2e7d32;">
                <?php echo $member_count; ?>
            </h3>
            <p>Active Members</p>
            <a href="users.php" class="btn" style="margin-top: 10px; font-size: 0.8rem;">Manage</a>
        </div>
    </div>

    <div style="margin-top: 3rem;">
        <h3>Quick Actions</h3>
        <div style="display: flex; gap: 10px; margin-top: 1rem; flex-wrap: wrap;">
            <a href="rounds.php" class="btn">Configure Rounds</a>
            <a href="results.php" class="btn">Enter Results</a>
            <a href="reports.php" class="btn">View Reports</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>