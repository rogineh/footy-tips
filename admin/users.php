<?php
require_once '../includes/auth.php';
require_admin();
$page_title = "Manage Users";
require_once '../includes/header.php';

$msg = '';

// Handle Approval
if (isset($_GET['approve'])) {
    $user_id = (int) $_GET['approve'];
    $stmt = $conn->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $msg = "User approved successfully!";
    }
    $stmt->close();
}

// Handle Admin Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_approved, has_nrl, has_afl) VALUES (?, ?, ?, 'admin', 1, 1, 1)");
        $stmt->bind_param("sss", $username, $email, $hashed);
        if ($stmt->execute()) {
            $msg = "Admin user created successfully!";
        } else {
            $msg = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get pending members
$pending_members = $conn->query("SELECT * FROM users WHERE is_approved = 0 AND role = 'member'");

// Get all members
$active_members = $conn->query("SELECT * FROM users WHERE is_approved = 1 AND role = 'member'");

?>

<div class="card">
    <h2>User Management</h2>

    <?php if ($msg): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <section>
        <h3>Pending Registrations</h3>
        <?php if ($pending_members->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Display Name</th>
                        <th>Email</th>
                        <th>Sports</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $pending_members->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['display_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php echo $user['has_nrl'] ? 'NRL ' : ''; ?>
                                <?php echo $user['has_afl'] ? 'AFL' : ''; ?>
                            </td>
                            <td>
                                <a href="users.php?approve=<?php echo $user['id']; ?>" class="btn btn-accent"
                                    style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Approve</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending registrations.</p>
        <?php endif; ?>
    </section>

    <section style="margin-top: 3rem;">
        <h3>Create New Admin</h3>
        <form action="users.php" method="POST"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="create_admin" style="padding: 0.8rem;">Create Admin</button>
        </form>
    </section>

    <section style="margin-top: 3rem;">
        <h3>Active Members</h3>
        <table>
            <thead>
                <tr>
                    <th>Display Name</th>
                    <th>Email</th>
                    <th>Sports</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $active_members->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['display_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php echo $user['has_nrl'] ? 'NRL ' : ''; ?>
                            <?php echo $user['has_afl'] ? 'AFL' : ''; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>