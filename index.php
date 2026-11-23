<?php
require 'includes/init.php';
wajib_login();
$judul = 'Dashboard Kasir';
$hari = $db->query("SELECT COUNT(*) n, COALESCE(SUM(total),0) t FROM transaksi WHERE DATE(tanggal)=CURDATE()")->fetch_assoc();
$jmlProduk = $db->query("SELECT COUNT(*) n FROM produk")->fetch_assoc()['n'];
$menipis = $db->query("SELECT * FROM produk WHERE stok <= 10 ORDER BY stok LIMIT 8")->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>
<p class="muted">Halo, <?= e($_SESSION['kasir']['nama']) ?>. Selamat bekerja!</p>
<div class="grid stats">
  <div class="card stat"><div class="l">Penjualan hari ini</div><div class="n"><?= rupiah($hari['t']) ?></div></div>
  <div class="card stat"><div class="l">Transaksi hari ini</div><div class="n"><?= (int)$hari['n'] ?></div></div>
  <div class="card stat"><div class="l">Jumlah produk</div><div class="n"><?= (int)$jmlProduk ?></div></div>
</div>
<div class="row" style="margin-bottom:1rem">
  <a class="btn" href="transaksi.php">🛒 Transaksi Baru</a>
  <a class="btn sec" href="riwayat.php">Riwayat</a>
  <a class="btn sec" href="produk.php">Kelola Produk</a>
  <a class="btn sec" href="laporan.php">Laporan</a>
</div>
<div class="card">
  <h2>Stok menipis (≤ 10)</h2>
  <?php if (!$menipis): ?><p class="muted">Semua stok aman.</p><?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>Produk</th><th>Kategori</th><th class="r">Stok</th></tr>
    <?php foreach ($menipis as $p): ?>
      <tr><td><?= e($p['nama_produk']) ?></td><td><?= e($p['kategori']) ?></td><td class="r"><span class="badge low"><?= (int)$p['stok'] ?></span></td></tr>
    <?php endforeach; ?>
  </table></div><?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
