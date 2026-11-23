<?php
require 'includes/init.php';
wajib_login();
$id = (int)($_GET['id'] ?? 0);
$judul = 'Riwayat Transaksi';

if ($id) {   // melihat detail transaksi
    $t = Struk::ambil($id);
    if (!$t) { flash('danger', 'Transaksi tidak ditemukan.'); redirect('riwayat.php'); }
    $items = DetailTransaksi::daftar($id);
    $judul = 'Detail Transaksi #' . $id;
    include 'includes/header.php';
?>
<div class="card">
  <p><strong>Tanggal:</strong> <?= e($t['tanggal']) ?> &nbsp; <strong>Kasir:</strong> <?= e($t['nama_kasir']) ?> &nbsp; <strong>Metode:</strong> <?= e($t['metode_pembayaran']) ?></p>
  <div class="table-wrap"><table>
    <tr><th>Barang</th><th class="r">Harga</th><th class="r">Qty</th><th class="r">Subtotal</th></tr>
    <?php foreach ($items as $d): ?>
      <tr><td><?= e($d['nama_produk']) ?></td><td class="r"><?= rupiah($d['harga']) ?></td><td class="r"><?= (int)$d['jumlah'] ?></td><td class="r"><?= rupiah($d['subtotal']) ?></td></tr>
    <?php endforeach; ?>
    <tr><th colspan="3" class="r">Total</th><th class="r"><?= rupiah($t['total']) ?></th></tr>
    <tr><td colspan="3" class="r">Bayar</td><td class="r"><?= rupiah($t['bayar']) ?></td></tr>
    <tr><td colspan="3" class="r">Kembalian</td><td class="r"><?= rupiah($t['kembalian']) ?></td></tr>
  </table></div>
  <div class="row" style="margin-top:1rem"><a class="btn sec" href="riwayat.php">← Kembali</a><a class="btn" href="struk.php?id=<?= $id ?>">Lihat / Cetak Struk</a></div>
</div>
<?php include 'includes/footer.php'; exit; }

$dari = $_GET['dari'] ?? ''; $sampai = $_GET['sampai'] ?? '';
$data = Transaksi::riwayat($dari, $sampai);
include 'includes/header.php';
?>
<form class="card filters" method="get">
  <div><label>Dari tanggal</label><input type="date" name="dari" value="<?= e($dari) ?>"></div>
  <div><label>Sampai tanggal</label><input type="date" name="sampai" value="<?= e($sampai) ?>"></div>
  <div class="row"><button class="btn">Filter</button><a class="btn sec" href="riwayat.php">Reset</a></div>
</form>
<div class="card"><div class="table-wrap"><table>
  <tr><th>No</th><th>Tanggal</th><th>Kasir</th><th class="r">Total</th><th class="r">Bayar</th><th></th></tr>
  <?php foreach ($data as $t): ?>
  <tr><td>#<?= $t['id_transaksi'] ?></td><td><?= e(date('d/m/Y H:i', strtotime($t['tanggal']))) ?></td><td><?= e($t['nama_kasir']) ?></td>
    <td class="r"><?= rupiah($t['total']) ?></td><td class="r"><?= rupiah($t['bayar']) ?></td>
    <td><a class="btn sm sec" href="riwayat.php?id=<?= $t['id_transaksi'] ?>">Detail</a></td></tr>
  <?php endforeach; if (!$data): ?><tr><td colspan="6" class="muted">Belum ada transaksi.</td></tr><?php endif; ?>
</table></div></div>
<?php include 'includes/footer.php'; ?>
