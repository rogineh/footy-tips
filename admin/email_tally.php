<?php
require_once '../includes/auth.php';
require_admin();
require_once '../includes/functions.php';
$page_title = "Email Tally";
require_once '../includes/header.php';

$sport = $_GET['sport'] ?? '';
if ($sport !== 'NRL' && $sport !== 'AFL') {
    die("Invalid sport.");
}

$msg = '';
$tally = get_tally($conn, $sport);

if (isset($_POST['send_emails'])) {
    $subject = "$sport Footy Tips Tally Update";
    $body = "Current Tally for $sport:\n\n";
    foreach ($tally as $index => $t) {
        $rank = $index + 1;
        $body .= "$rank. {$t['display_name']}: {$t['points']} points\n";
    }

    // Get all approved members for this sport
    $sport_col = ($sport == 'NRL') ? 'has_nrl' : 'has_afl';
    $recipients = $conn->query("SELECT email FROM users WHERE $sport_col = 1 AND is_approved = 1 AND role = 'member'");

    $count = 0;
    while ($row = $recipients->fetch_assoc()) {
        $to = $row['email'];
        // In HostGator, you'd use mail() or a library like PHPMailer.
        // For now, we simulate the send.
        // mail($to, $subject, $body, "From: " . ADMIN_EMAIL);
        $count++;
    }

    $msg = "Emails simulated for $count members of $sport!";
}
?>

<div class="card">
    <h2>Email Tally:
        <?php echo $sport; ?>
    </h2>

    <?php if ($msg): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="card" style="background: #f9f9f9;">
        <h3>Email Preview</h3>
        <p><strong>Subject:</strong>
            <?php echo "$sport Footy Tips Tally Update"; ?>
        </p>
        <pre style="background: #eee; padding: 1rem; border-radius: 5px; font-family: monospace;">
Current Tally for <?php echo $sport; ?>:

<?php foreach ($tally as $index => $t): ?>
        <?php echo ($index + 1); ?>. <?php echo $t['display_name']; ?>: <?php echo $t['points']; ?> points
<?php endforeach; ?>
        </pre>
    </div>

    <form action="" method="POST" style="margin-top: 2rem;">
        <button type="submit" name="send_emails" class="btn">Send Emails to All
            <?php echo $sport; ?> Members
        </button>
        <a href="reports.php" class="btn btn-accent" style="margin-left: 10px;">Back to Reports</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>