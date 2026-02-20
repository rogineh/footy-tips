<?php
$page_title = "Manage Rounds";
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_admin();

$msg = '';

// Handle Round Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_round'])) {
    $sport = $_POST['sport'];
    $year = (int) $_POST['year'];
    $round_number = (int) $_POST['round_number'];
    $deadline = $_POST['deadline'];

    $stmt = $conn->prepare("INSERT INTO rounds (sport, year, round_number, deadline) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $sport, $year, $round_number, $deadline);
    if ($stmt->execute()) {
        $msg = "Round $round_number ($sport) created successfully!";
    } else {
        $msg = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle Add Game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $round_id = (int) $_POST['round_id'];
    $home_team = trim($_POST['home_team']);
    $away_team = trim($_POST['away_team']);
    $game_time = $_POST['game_time'];
    $is_first = isset($_POST['is_first']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO games (round_id, home_team, away_team, game_time, is_first_game) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $round_id, $home_team, $away_team, $game_time, $is_first);
    if ($stmt->execute()) {
        $msg = "Game added successfully!";
    }
    $stmt->close();
}

// Handle Add Extra Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $round_id = (int) $_POST['round_id'];
    $text = trim($_POST['question_text']);
    $type = $_POST['question_type'];
    $range = (int) $_POST['scoring_range'];

    $stmt = $conn->prepare("INSERT INTO extra_questions (round_id, question_text, question_type, scoring_range) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $round_id, $text, $type, $range);
    if ($stmt->execute()) {
        $msg = "Extra question added!";
    }
    $stmt->close();
}

// Get all rounds
$rounds = $conn->query("SELECT * FROM rounds ORDER BY year DESC, sport, round_number DESC");

?>

<div class="card">
    <h2>Round Management</h2>

    <?php if ($msg): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <section>
        <h3>Create New Round</h3>
        <form action="rounds.php" method="POST"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Sport</label>
                <select name="sport">
                    <option value="NRL">NRL</option>
                    <option value="AFL">AFL</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Year</label>
                <input type="number" name="year" value="<?php echo date('Y'); ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Round #</label>
                <input type="number" name="round_number" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Deadline</label>
                <input type="datetime-local" name="deadline" required>
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" name="create_round" style="width: 100%;">Create Round</button>
            </div>
        </form>
    </section>

    <section style="margin-top: 3rem;">
        <h3>Existing Rounds</h3>
        <?php if ($rounds->num_rows > 0): ?>
            <?php while ($r = $rounds->fetch_assoc()): ?>
                <div class="card" style="background: #fff; margin-bottom: 1.5rem; border: 1px solid #eee;">
                    <h4 style="display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <?php echo $r['sport']; ?> - Round
                            <?php echo $r['round_number']; ?> (
                            <?php echo $r['year']; ?>)
                        </span>
                        <small style="font-weight: normal; font-size: 0.8rem;">Deadline:
                            <?php echo $r['deadline']; ?>
                        </small>
                    </h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 1rem;">
                        <!-- Games List -->
                        <div>
                            <h5>Games</h5>
                            <ul style="list-style: none; font-size: 0.9rem; margin-top: 0.5rem;">
                                <?php
                                $games = $conn->query("SELECT * FROM games WHERE round_id = " . $r['id']);
                                while ($g = $games->fetch_assoc()): ?>
                                    <li style="padding: 5px 0; border-bottom: 1px solid #f9f9f9;">
                                        <?php echo htmlspecialchars($g['home_team']); ?> vs
                                        <?php echo htmlspecialchars($g['away_team']); ?>
                                        <?php if ($g['is_first_game']): ?> <span style="color: #c62828;">(First)</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>

                            <!-- Add Game Form -->
                            <form action="rounds.php" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="round_id" value="<?php echo $r['id']; ?>">
                                <input type="text" name="home_team" placeholder="Home Team" required
                                    style="padding: 5px; font-size: 0.8rem; margin-bottom: 5px;">
                                <input type="text" name="away_team" placeholder="Away Team" required
                                    style="padding: 5px; font-size: 0.8rem; margin-bottom: 5px;">
                                <input type="datetime-local" name="game_time" required
                                    style="padding: 5px; font-size: 0.8rem; margin-bottom: 5px;">
                                <label style="font-size: 0.8rem;"><input type="checkbox" name="is_first"> First Game?</label>
                                <button type="submit" name="add_game" class="btn"
                                    style="padding: 5px 10px; font-size: 0.7rem;">Add Game</button>
                            </form>
                        </div>

                        <!-- Questions List -->
                        <div>
                            <h5>Extra Questions</h5>
                            <ul style="list-style: none; font-size: 0.9rem; margin-top: 0.5rem;">
                                <?php
                                $questions = $conn->query("SELECT * FROM extra_questions WHERE round_id = " . $r['id']);
                                while ($q = $questions->fetch_assoc()): ?>
                                    <li style="padding: 5px 0; border-bottom: 1px solid #f9f9f9;">
                                        <?php echo htmlspecialchars($q['question_text']); ?> (
                                        <?php echo $q['question_type']; ?>) ±
                                        <?php echo $q['scoring_range']; ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>

                            <!-- Add Question Form -->
                            <form action="rounds.php" method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="round_id" value="<?php echo $r['id']; ?>">
                                <div class="form-group" style="margin-bottom: 5px;">
                                    <input type="text" name="question_text" placeholder="Question text" required
                                        style="padding: 5px; font-size: 0.8rem;">
                                </div>
                                <div class="form-group" style="margin-bottom: 5px;">
                                    <select name="question_type" style="padding: 5px; font-size: 0.8rem;">
                                        <option value="margin">Margin (Numerical)</option>
                                        <option value="total_score">Total Score (Numerical)</option>
                                        <option value="random">Random (Text)</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 5px;">
                                    <input type="number" name="scoring_range" placeholder="Range (e.g. 5)" value="0"
                                        style="padding: 5px; font-size: 0.8rem;">
                                </div>
                                <button type="submit" name="add_question" class="btn btn-accent"
                                    style="padding: 5px 10px; font-size: 0.7rem;">Add Question</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No rounds configured yet.</p>
        <?php endif; ?>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>