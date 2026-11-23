<?php
require 'includes/init.php';
if (!empty($_SESSION['kasir'])) redirect('index.php');
csrf_check();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($user === '' || $pass === '') {
        $error = 'Nama pengguna dan password wajib diisi.';
    } elseif ((new Kasir())->login($user, $pass)) {   // verifikasi data pengguna
        redirect('index.php');                         // login berhasil -> dashboard
    } else {
        $error = 'Login gagal: data pengguna tidak valid.';
    }
}
$info = flash();
?><!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Aplikasi Kasir</title>
<link rel="stylesheet" href="assets/css/style.css">
</head><body class="login-page">
<form class="login-box" method="post" autocomplete="off">
  <h1>🧮 Login Kasir</h1>
  <?php if ($info): ?><div class="alert <?= e($info[0]) ?>"><?= e($info[1]) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label for="username">Nama pengguna</label>
  <input id="username" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
  <label for="password">Password</label>
  <input id="password" name="password" type="password" required>
  <br><button class="btn block">Masuk</button>
  <p class="muted" style="text-align:center;font-size:.8rem">Awal: admin / password</p>
</form>
</body></html>
