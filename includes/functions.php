<?php
/**
 * Core Application Functions
 */

/**
 * Calculate total points for a user for a specific sport
 */
function get_user_total_points($conn, $user_id, $sport)
{
    $points = 0;

    // 1. Points from Games
    $sql = "SELECT COUNT(*) as correct_tips 
            FROM tips t
            JOIN games g ON t.game_id = g.id
            JOIN rounds r ON g.round_id = r.id
            JOIN results res ON g.id = res.game_id
            WHERE t.user_id = ? AND r.sport = ? AND t.winner_id = res.winner_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $sport);
    $stmt->execute();
    $points += $stmt->get_result()->fetch_assoc()['correct_tips'];
    $stmt->close();

    // 2. Points from Extra Questions
    $sql = "SELECT eq.id, eq.scoring_range, eq.question_type, et.answer, er.correct_answer
            FROM extra_tips et
            JOIN extra_questions eq ON et.extra_question_id = eq.id
            JOIN rounds r ON eq.round_id = r.id
            JOIN extra_results er ON eq.id = er.extra_question_id
            WHERE et.user_id = ? AND r.sport = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $sport);
    $stmt->execute();
    $extras = $stmt->get_result();

    while ($row = $extras->fetch_assoc()) {
        if ($row['scoring_range'] > 0) {
            // Range logic (for numerical answers)
            $user_ans = (int) $row['answer'];
            $corr_ans = (int) $row['correct_answer'];
            if (abs($user_ans - $corr_ans) <= $row['scoring_range']) {
                $points++;
            }
        } else {
            // Exact match logic
            if (trim(strtolower($row['answer'])) == trim(strtolower($row['correct_answer']))) {
                $points++;
            }
        }
    }
    $stmt->close();

    return $points;
}

/**
 * Get Round Points for a user
 */
function get_user_round_points($conn, $user_id, $round_id)
{
    $points = 0;

    // Game points
    $sql = "SELECT COUNT(*) as correct_tips 
            FROM tips t
            JOIN games g ON t.game_id = g.id
            JOIN results res ON g.id = res.game_id
            WHERE t.user_id = ? AND g.round_id = ? AND t.winner_id = res.winner_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $round_id);
    $stmt->execute();
    $points += $stmt->get_result()->fetch_assoc()['correct_tips'];
    $stmt->close();

    // Extra points
    $sql = "SELECT eq.scoring_range, et.answer, er.correct_answer
            FROM extra_tips et
            JOIN extra_questions eq ON et.extra_question_id = eq.id
            JOIN extra_results er ON eq.id = er.extra_question_id
            WHERE et.user_id = ? AND eq.round_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $round_id);
    $stmt->execute();
    $extras = $stmt->get_result();
    while ($row = $extras->fetch_assoc()) {
        if ($row['scoring_range'] > 0) {
            if (abs((int) $row['answer'] - (int) $row['correct_answer']) <= $row['scoring_range'])
                $points++;
        } else {
            if (trim(strtolower($row['answer'])) == trim(strtolower($row['correct_answer'])))
                $points++;
        }
    }
    $stmt->close();

    return $points;
}

/**
 * Get Tally for a sport
 */
function get_tally($conn, $sport)
{
    $column = ($sport == 'NRL') ? 'has_nrl' : 'has_afl';
    $users = $conn->query("SELECT id, display_name FROM users WHERE $column = 1 AND is_approved = 1 AND role = 'member'");

    $tally = [];
    while ($u = $users->fetch_assoc()) {
        $u['points'] = get_user_total_points($conn, $u['id'], $sport);
        $tally[] = $u;
    }

    // Sort by points descending
    usort($tally, function ($a, $b) {
        return $b['points'] - $a['points'];
    });

    return $tally;
}
?>