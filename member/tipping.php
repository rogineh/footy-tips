<?php
require_once '../includes/auth.php';
require_member();
require_once '../includes/functions.php';
$page_title = "Enter Tips";
require_once '../includes/header.php';

$uid = $_SESSION['user_id'];
$round_id = isset($_GET['round_id']) ? (int) $_GET['round_id'] : 0;

if (!$round_id) {
    echo "<div class='card'><p>Please select a round from your dashboard.</p></div>";
    require_once '../includes/footer.php';
    exit;
}

// Get round info
$round_stmt = $conn->prepare("SELECT * FROM rounds WHERE id = ?");
$round_stmt->bind_param("i", $round_id);
$round_stmt->execute();
$round = $round_stmt->get_result()->fetch_assoc();
$round_stmt->close();

if (!$round) {
    die("Round not found.");
}

$deadline_passed = strtotime($round['deadline']) < time();
$msg = '';

// Handle Tips Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$deadline_passed) {
    // Save Game Tips
    if (isset($_POST['game_tips'])) {
        foreach ($_POST['game_tips'] as $game_id => $winner_id) {
            $stmt = $conn->prepare("INSERT INTO tips (user_id, game_id, winner_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE winner_id = VALUES(winner_id)");
            $stmt->bind_param("iii", $uid, $game_id, $winner_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Save Extra Tips
    if (isset($_POST['extra_tips'])) {
        foreach ($_POST['extra_tips'] as $eq_id => $answer) {
            $stmt = $conn->prepare("INSERT INTO extra_tips (user_id, extra_question_id, answer) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE answer = VALUES(answer)");
            $stmt->bind_param("iis", $uid, $eq_id, $answer);
            $stmt->execute();
            $stmt->close();
        }
    }
    $msg = "Tips saved successfully!";
}

// Get games for this round
$games = $conn->query("SELECT g.*, t.winner_id FROM games g LEFT JOIN tips t ON g.id = t.game_id AND t.user_id = $uid WHERE g.round_id = $round_id");

// Get extra questions
$questions = $conn->query("SELECT eq.*, et.answer FROM extra_questions eq LEFT JOIN extra_tips et ON eq.id = et.extra_question_id AND et.user_id = $uid WHERE eq.round_id = $round_id");

?>

<div class="card">
    <h2>
        <?php echo $round['sport']; ?> - Round
        <?php echo $round['round_number']; ?> Tips
    </h2>
    <p>Deadline: <strong style="color: #c62828;">
            <?php echo $round['deadline']; ?>
        </strong></p>

    <?php if ($deadline_passed): ?>
        <div style="background: #fff3e0; color: #e65100; padding: 0.8rem; border-radius: 8px; margin-bottom: 2rem;">
            The deadline for this round has passed. You can no longer modify your tips.
        </div>
    <?php elseif ($msg): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 0.8rem; border-radius: 8px; margin-bottom: 2rem;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <section>
            <h3>Match Tipping</h3>
            <table>
                <thead>
                    <tr>
                        <th>Game</th>
                        <th>Your Choice</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($g = $games->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($g['home_team']); ?> vs
                                <?php echo htmlspecialchars($g['away_team']); ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 15px;">
                                    <label style="font-weight: normal; cursor: pointer;">
                                        <input type="radio" name="game_tips[<?php echo $g['id']; ?>]" value="1" <?php echo ($g['winner_id'] == 1) ? 'checked' : ''; ?>
                                        <?php echo $deadline_passed ? 'disabled' : ''; ?>>
                                        <?php echo htmlspecialchars($g['home_team']); ?>
                                    </label>
                                    <label style="font-weight: normal; cursor: pointer;">
                                        <input type="radio" name="game_tips[<?php echo $g['id']; ?>]" value="2" <?php echo ($g['winner_id'] == 2) ? 'checked' : ''; ?>
                                        <?php echo $deadline_passed ? 'disabled' : ''; ?>>
                                        <?php echo htmlspecialchars($g['away_team']); ?>
                                    </label>
                                    <label style="font-weight: normal; cursor: pointer;">
                                        <input type="radio" name="game_tips[<?php echo $g['id']; ?>]" value="0" <?php echo ($g['winner_id'] === '0') ? 'checked' : ''; ?>
                                        <?php echo $deadline_passed ? 'disabled' : ''; ?>>
                                        Draw
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section style="margin-top: 3rem;">
            <h3>Extra Questions</h3>
            <?php while ($q = $questions->fetch_assoc()): ?>
                <div class="form-group">
                    <label>
                        <?php echo htmlspecialchars($q['question_text']); ?>
                    </label>
                    <input type="<?php echo ($q['question_type'] == 'random') ? 'text' : 'number'; ?>"
                        name="extra_tips[<?php echo $q['id']; ?>]" value="<?php echo htmlspecialchars($q['answer']); ?>"
                        placeholder="<?php echo ($q['question_type'] == 'random') ? 'Enter text...' : 'Enter number...'; ?>"
                        <?php echo $deadline_passed ? 'readonly' : ''; ?>>
                </div>
            <?php endwhile; ?>
        </section>

        <?php if (!$deadline_passed): ?>
            <button type="submit" style="margin-top: 2rem; width: 100%;">Save All Tips</button>
        <?php endif; ?>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>