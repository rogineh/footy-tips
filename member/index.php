<?php
require_once '../includes/auth.php';
require_member();
require_once '../includes/functions.php';
$page_title = "Dashboard";
require_once '../includes/header.php';

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();

// Get next deadlines
$next_nrl = $user['has_nrl'] ? $conn->query("SELECT * FROM rounds WHERE sport = 'NRL' AND deadline > NOW() ORDER BY deadline ASC LIMIT 1")->fetch_assoc() : null;
$next_afl = $user['has_afl'] ? $conn->query("SELECT * FROM rounds WHERE sport = 'AFL' AND deadline > NOW() ORDER BY deadline ASC LIMIT 1")->fetch_assoc() : null;

?>

<div class="card">
    <h2>Welcome,
        <?php echo htmlspecialchars($user['display_name']); ?>!
    </h2>
    <p>Good luck with your footy tips this week.</p>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 2rem;">
        <?php if ($user['has_nrl']): ?>
            <div class="card" style="margin-top: 0; background: #e8f5e9;">
                <h3 style="color: #2e7d32;">NRL Tipping</h3>
                <?php if ($next_nrl): ?>
                    <p>Next Round: <strong>
                            <?php echo $next_nrl['round_number']; ?>
                        </strong></p>
                    <p>Deadline: <span style="color: #c62828;">
                            <?php echo $next_nrl['deadline']; ?>
                        </span></p>
                    <a href="tipping.php?round_id=<?php echo $next_nrl['id']; ?>" class="btn" style="margin-top: 10px;">Enter
                        Tips</a>
                <?php else: ?>
                    <p>No upcoming rounds scheduled.</p>
                <?php endif; ?>
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                <p>Your Points: <strong>
                        <?php echo get_user_total_points($conn, $uid, 'NRL'); ?>
                    </strong></p>
            </div>
        <?php endif; ?>

        <?php if ($user['has_afl']): ?>
            <div class="card" style="margin-top: 0; background: #fffde7;">
                <h3 style="color: #fbc02d;">AFL Tipping</h3>
                <?php if ($next_afl): ?>
                    <p>Next Round: <strong>
                            <?php echo $next_afl['round_number']; ?>
                        </strong></p>
                    <p>Deadline: <span style="color: #c62828;">
                            <?php echo $next_afl['deadline']; ?>
                        </span></p>
                    <a href="tipping.php?round_id=<?php echo $next_afl['id']; ?>" class="btn" style="margin-top: 10px;">Enter
                        Tips</a>
                <?php else: ?>
                    <p>No upcoming rounds scheduled.</p>
                <?php endif; ?>
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                <p>Your Points: <strong>
                        <?php echo get_user_total_points($conn, $uid, 'AFL'); ?>
                    </strong></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>