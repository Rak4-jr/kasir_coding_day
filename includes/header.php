<?php
$sk = $_SESSION['kasir'];
$cur = basename($_SERVER['SCRIPT_NAME']);
$nav = [
  'index.php'     => ['Dashboard', '🏠'],
  'transaksi.php' => ['Transaksi', '🛒'],
  'riwayat.php'   => ['Riwayat', '🧾'],
  'produk.php'    => ['Produk', '📦'],
  'laporan.php'   => ['Laporan', '📊'],
];
$alias = ['pembayaran.php' => 'transaksi.php', 'struk.php' => 'transaksi.php'];
$aktif = $alias[$cur] ?? $cur;
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'Kasir') ?> - Aplikasi Kasir</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="topbar">
  <button class="burger" id="burger" aria-label="Menu">☰</button>
  <strong>🧮 Aplikasi Kasir</strong>
</div>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <div class="brand">🧮 Aplikasi Kasir</div>
    <nav>
      <?php foreach ($nav as $file => [$label, $ikon]): ?>
        <a href="<?= $file ?>" class="<?= $aktif === $file ? 'active' : '' ?>"><span><?= $ikon ?></span> <?= $label ?></a>
      <?php endforeach; ?>
      <a href="logout.php" class="logout"><span>🚪</span> Logout</a>
    </nav>
    <div class="user">Masuk sebagai<br><strong><?= e($sk['nama']) ?></strong></div>
  </aside>
  <main class="content">
    <h1><?= e($judul ?? '') ?></h1>
    <?php if ($f = flash()): ?>
      <div class="alert <?= e($f[0]) ?>"><?= e($f[1]) ?></div>
    <?php endif; ?>
