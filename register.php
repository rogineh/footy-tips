<?php
$page_title = "Register Member";
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $display_name = trim($_POST['display_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $has_nrl = isset($_POST['has_nrl']) ? 1 : 0;
    $has_afl = isset($_POST['has_afl']) ? 1 : 0;

    if (empty($username) || empty($email) || empty($password) || empty($display_name)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!$has_nrl && !$has_afl) {
        $error = "Please select at least one sport (NRL or AFL).";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, display_name, has_nrl, has_afl, is_approved) VALUES (?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("ssssii", $username, $email, $hashed_password, $display_name, $has_nrl, $has_afl);

            if ($stmt->execute()) {
                $success = "Registration successful! Please wait for an admin to approve your account.";
            } else {
                $error = "Error during registration. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <h2 style="text-align: center;">Member Registration</h2>

    <?php if ($error): ?>
        <div style="background: #ffebee; color: #c62828; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $success; ?>
        </div>
        <p style="text-align: center;"><a href="index.php" class="btn">Back to Login</a></p>
    <?php else: ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="display_name">Display Name (Visible in Tally)</label>
                <input type="text" id="display_name" name="display_name" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label>Participate in:</label>
                <div style="display: flex; gap: 20px; align-items: center; margin-top: 5px;">
                    <label style="display: flex; align-items: center; gap: 5px; font-weight: normal; margin-bottom: 0;">
                        <input type="checkbox" name="has_nrl" checked> NRL
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px; font-weight: normal; margin-bottom: 0;">
                        <input type="checkbox" name="has_afl"> AFL
                    </label>
                </div>
            </div>

            <button type="submit" style="width: 100%;">Register</button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem;">
            Already have an account? <a href="index.php">Login here</a>
        </p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>