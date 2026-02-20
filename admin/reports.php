<?php
require_once '../includes/auth.php';
require_admin();
require_once '../includes/functions.php';
$page_title = "Admin Reports";
require_once '../includes/header.php';

$rounds = $conn->query("SELECT * FROM rounds ORDER BY year DESC, sport, round_number DESC");

?>

<div class="card">
    <h2>Admin Reports</h2>

    <section>
        <h3>Submission Summary by Round</h3>
        <table>
            <thead>
                <tr>
                    <th>Sport & Round</th>
                    <th>Deadline</th>
                    <th>Submissions</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $rounds->fetch_assoc()): ?>
                    <?php
                    // Count unique users who tipped in this round
                    $count_res = $conn->query("SELECT COUNT(DISTINCT user_id) FROM tips t JOIN games g ON t.game_id = g.id WHERE g.round_id = " . $r['id']);
                    $count = $count_res->fetch_row()[0];
                    ?>
                    <tr>
                        <td><?php echo $r['sport']; ?> - Round <?php echo $r['round_number']; ?></td>
                        <td><?php echo $r['deadline']; ?></td>
                        <td><?php echo $count; ?> members</td>
                        <td>
                            <a href="reports.php?round_id=<?php echo $r['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.7rem;">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>

    <?php if (isset($_GET['round_id'])): ?>
        <?php 
        $rid = (int)$_GET['round_id']; 
        $r_info = $conn->query("SELECT * FROM rounds WHERE id = $rid")->fetch_assoc();
        ?>
        <section style="margin-top: 3rem;" id="details">
            <h3>Submission Details: <?php echo $r_info['sport']; ?> - Round <?php echo $r_info['round_number']; ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Status</th>
                        <th>Points Acquired</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sport_col = $r_info['sport'] == 'NRL' ? 'has_nrl' : 'has_afl';
                    $users = $conn->query("SELECT id, display_name FROM users WHERE $sport_col = 1 AND is_approved = 1 AND role = 'member'");
                    while ($u = $users->fetch_assoc()):
                        // Check if they tipped
                        $tipped = $conn->query("SELECT 1 FROM tips t JOIN games g ON t.game_id = g.id WHERE t.user_id = {$u['id']} AND g.round_id = $rid LIMIT 1")->num_rows > 0;
                        $pts = get_user_round_points($conn, $u['id'], $rid);
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['display_name']); ?></td>
                            <td><?php echo $tipped ? '<span style="color: green;">Submitted</span>' : '<span style="color: red;">Not Submitted</span>'; ?></td>
                            <td><?php echo $pts; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>

    <section style="margin-top: 3rem;">
        <h3>Current Tally</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4>NRL Tally</h4>
                <table>
                    <thead><tr><th>Member</th><th>Points</th></tr></thead>
                    <tbody>
                        <?php foreach (get_tally($conn, 'NRL') as $t): ?>
                            <tr><td><?php echo htmlspecialchars($t['display_name']); ?></td><td><?php echo $t['points']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="email_tally.php?sport=NRL" class="btn btn-accent" style="margin-top: 10px; font-size: 0.8rem;">Email NRL Tally</a>
            </div>
            <div>
                <h4>AFL Tally</h4>
                <table>
                    <thead><tr><th>Member</th><th>Points</th></tr></thead>
                    <tbody>
                        <?php foreach (get_tally($conn, 'AFL') as $t): ?>
                            <tr><td><?php echo htmlspecialchars($t['display_name']); ?></td><td><?php echo $t['points']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="email_tally.php?sport=AFL" class="btn btn-accent" style="margin-top: 10px; font-size: 0.8rem;">Email AFL Tally</a>
            </div>
        </div>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>
