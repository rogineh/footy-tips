<?php
/**
 * Authentication Helpers
 */

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header("Location: /index.php");
        exit;
    }
}

function require_admin()
{
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: /member/index.php");
        exit;
    }
}

function require_member()
{
    require_login();
    if ($_SESSION['role'] !== 'member') {
        header("Location: /admin/index.php");
        exit;
    }
}

function logout()
{
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header("Location: /index.php");
    exit;
}
?>