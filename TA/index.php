<?php
// index.php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/lib/users.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');

    if ($u && isset($USERS[$u])) {
        $record = $USERS[$u];
        if (password_verify($p, $record['password']) || $p === $record['password']) {
            $_SESSION['user'] = $record['name'] ?: $u;
            $_SESSION['username'] = $u;
            $_SESSION['role'] = $record['role'];
            header('Location: dashboard.php');
            exit;
        }
    }

    $err = 'Username atau password salah';
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SmartLibrary - Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="bg">
<main class="center-card login-page">
    <div class="card">
        <h2 class="brand">SmartLibrary</h2>
        <?php if($err): ?><div class="alert"><?=$err?></div><?php endif; ?>
        <form method="post" class="form">
            <label>Username</label>
            <input name="username" type="text" required autofocus>
            <label>Password</label>
            <input name="password" type="password" required>
            <button class="btn" type="submit">Login</button>
        </form>
        <p class="muted">Default accounts: <b>admin</b> / <b>admin123</b> &nbsp;|&nbsp; <b>mahasiswa</b> / <b>mhs123</b></p>
    </div>
    <footer class="foot">Praktikum  &amp; Tugas Akhir</footer>
</main>
</body>
</html>
