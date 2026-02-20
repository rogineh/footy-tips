<?php
require_once '../includes/auth.php';
require_member();
require_once '../includes/functions.php';
$page_title = "Points & Tally";
require_once '../includes/header.php';

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();

// Get recent rounds results for this member
$recent_rounds = $conn->query("SELECT r.* FROM rounds r JOIN games g ON r.id = g.id JOIN results res ON g.id = res.game_id GROUP BY r.id ORDER BY r.deadline DESC LIMIT 5");

?>

<div class="card">
    <h2>Points & Tally</h2>

    <section>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <?php if ($user['has_nrl']): ?>
                <div class="card" style="margin-top: 0;">
                    <h4>NRL Leaderboard</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (get_tally($conn, 'NRL') as $t): ?>
                                <tr <?php echo ($t['id'] == $uid) ? 'style="background: #fff8e1; font-weight: bold;"' : ''; ?>>
                                    <td>
                                        <?php echo htmlspecialchars($t['display_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo $t['points']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($user['has_afl']): ?>
                <div class="card" style="margin-top: 0;">
                    <h4>AFL Leaderboard</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (get_tally($conn, 'AFL') as $t): ?>
                                <tr <?php echo ($t['id'] == $uid) ? 'style="background: #fff8e1; font-weight: bold;"' : ''; ?>>
                                    <td>
                                        <?php echo htmlspecialchars($t['display_name']); ?>
                                    </td>
                                    <td>
                                        <?php echo $t['points']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section style="margin-top: 3rem;">
        <h3>My Recent Round Performance</h3>
        <table>
            <thead>
                <tr>
                    <th>Round</th>
                    <th>Sport</th>
                    <th>Your Points</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $finished_rounds = $conn->query("SELECT DISTINCT r.* FROM rounds r JOIN games g ON r.id = g.round_id JOIN results res ON g.id = res.game_id ORDER BY r.deadline DESC");
                while ($r = $finished_rounds->fetch_assoc()):
                    if (($r['sport'] == 'NRL' && !$user['has_nrl']) || ($r['sport'] == 'AFL' && !$user['has_afl']))
                        continue;
                    ?>
                    <tr>
                        <td>Round
                            <?php echo $r['round_number']; ?> (
                            <?php echo $r['year']; ?>)
                        </td>
                        <td>
                            <?php echo $r['sport']; ?>
                        </td>
                        <td><strong>
                                <?php echo get_user_round_points($conn, $uid, $r['id']); ?>
                            </strong></td>
                        <td><a href="tipping.php?round_id=<?php echo $r['id']; ?>" class="btn"
                                style="padding: 5px 10px; font-size: 0.7rem;">View My Tips</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>