<?php
require 'includes/init.php';
wajib_login();
$id = (int)($_GET['id'] ?? 0);
$t = Struk::ambil($id);
if (!$t) { flash('danger', 'Struk tidak ditemukan.'); redirect('riwayat.php'); }
$items = DetailTransaksi::daftar($id);
$judul = 'Struk #' . $id;
include 'includes/header.php';
?>
<div class="struk" id="struk">
  <h2>TOKO KASIR</h2>
  <div class="c">Struk Pembelian</div>
  <hr>
  <div class="ln"><span>No</span><span>#<?= str_pad($t['id_transaksi'], 5, '0', STR_PAD_LEFT) ?></span></div>
  <div class="ln"><span>Tanggal</span><span><?= e(date('d/m/Y H:i', strtotime($t['tanggal']))) ?></span></div>
  <div class="ln"><span>Kasir</span><span><?= e($t['nama_kasir']) ?></span></div>
  <hr>
  <?php foreach ($items as $d): ?>
    <div><?= e($d['nama_produk']) ?></div>
    <div class="ln"><span><?= (int)$d['jumlah'] ?> x <?= number_format($d['harga'], 0, ',', '.') ?></span><span><?= number_format($d['subtotal'], 0, ',', '.') ?></span></div>
  <?php endforeach; ?>
  <hr>
  <div class="ln"><strong>TOTAL</strong><strong><?= rupiah($t['total']) ?></strong></div>
  <div class="ln"><span>Bayar (<?= e($t['metode_pembayaran']) ?>)</span><span><?= rupiah($t['bayar']) ?></span></div>
  <div class="ln"><span>Kembalian</span><span><?= rupiah($t['kembalian']) ?></span></div>
  <hr>
  <div class="c">Terima kasih atas kunjungan Anda</div>
</div>
<div class="row noprint" style="justify-content:center;margin-top:1rem">
  <button class="btn" onclick="window.print()">🖨️ Cetak Struk</button>
  <a class="btn green" href="transaksi.php">Transaksi Baru</a>
  <a class="btn sec" href="riwayat.php">Riwayat</a>
</div>
<?php include 'includes/footer.php'; ?>
