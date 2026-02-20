<?php
$page_title = "Enter Results";
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_admin();

$msg = '';

// Handle Game Result
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_game_result'])) {
    $game_id = (int) $_POST['game_id'];
    $home_score = (int) $_POST['home_score'];
    $away_score = (int) $_POST['away_score'];
    $winner_id = 0;
    if ($home_score > $away_score)
        $winner_id = 1;
    elseif ($away_score > $home_score)
        $winner_id = 2;

    $stmt = $conn->prepare("INSERT INTO results (game_id, winner_id, home_score, away_score) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE winner_id = VALUES(winner_id), home_score = VALUES(home_score), away_score = VALUES(away_score)");
    $stmt->bind_param("iiii", $game_id, $winner_id, $home_score, $away_score);
    if ($stmt->execute()) {
        $msg = "Game result saved!";
    }
    $stmt->close();
}

// Handle Extra Result
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_extra_result'])) {
    $question_id = (int) $_POST['extra_question_id'];
    $answer = trim($_POST['correct_answer']);

    $stmt = $conn->prepare("INSERT INTO extra_results (extra_question_id, correct_answer) VALUES (?, ?) ON DUPLICATE KEY UPDATE correct_answer = VALUES(correct_answer)");
    $stmt->bind_param("is", $question_id, $answer);
    if ($stmt->execute()) {
        $msg = "Extra question result saved!";
    }
    $stmt->close();
}

// Get rounds with games/questions
$rounds = $conn->query("SELECT * FROM rounds ORDER BY year DESC, sport, round_number DESC");

?>

<div class="card">
    <h2>Enter Results</h2>

    <?php if ($msg): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <p>Select a round to enter results for games and extra questions.</p>

    <?php while ($r = $rounds->fetch_assoc()): ?>
        <div class="card" style="background: #fff; margin-bottom: 2rem; border-left: 5px solid var(--primary-color);">
            <h4>
                <?php echo $r['sport']; ?> - Round
                <?php echo $r['round_number']; ?>
            </h4>

            <div style="margin-top: 1rem;">
                <h5>Game Results</h5>
                <table>
                    <thead>
                        <tr>
                            <th>Match</th>
                            <th>Home Score</th>
                            <th>Away Score</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $games = $conn->query("SELECT g.*, res.home_score, res.away_score FROM games g LEFT JOIN results res ON g.id = res.game_id WHERE g.round_id = " . $r['id']);
                        while ($g = $games->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($g['home_team']); ?> vs
                                    <?php echo htmlspecialchars($g['away_team']); ?>
                                </td>
                                <form action="results.php" method="POST">
                                    <input type="hidden" name="game_id" value="<?php echo $g['id']; ?>">
                                    <td><input type="number" name="home_score" value="<?php echo $g['home_score']; ?>"
                                            style="width: 60px; padding: 5px;"></td>
                                    <td><input type="number" name="away_score" value="<?php echo $g['away_score']; ?>"
                                            style="width: 60px; padding: 5px;"></td>
                                    <td><button type="submit" name="save_game_result" class="btn"
                                            style="padding: 5px 10px; font-size: 0.7rem;">Save</button></td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem;">
                <h5>Extra Question Results</h5>
                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Correct Answer</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $questions = $conn->query("SELECT q.*, res.correct_answer FROM extra_questions q LEFT JOIN extra_results res ON q.id = res.extra_question_id WHERE q.round_id = " . $r['id']);
                        while ($q = $questions->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($q['question_text']); ?> <small>(±
                                        <?php echo $q['scoring_range']; ?>)
                                    </small>
                                </td>
                                <form action="results.php" method="POST">
                                    <input type="hidden" name="extra_question_id" value="<?php echo $q['id']; ?>">
                                    <td><input type="text" name="correct_answer"
                                            value="<?php echo htmlspecialchars($q['correct_answer']); ?>" style="padding: 5px;">
                                    </td>
                                    <td><button type="submit" name="save_extra_result" class="btn btn-accent"
                                            style="padding: 5px 10px; font-size: 0.7rem;">Save</button></td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once '../includes/footer.php'; ?>