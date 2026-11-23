<?php
require 'includes/init.php';
wajib_login();
csrf_check();
$trx = new Transaksi();
$keranjang = $trx->buatTransaksi();
if (!$keranjang) { flash('warning', 'Keranjang kosong. Pilih barang terlebih dahulu.'); redirect('transaksi.php'); }
$total = $trx->hitungTotal($keranjang);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bayar  = (float)($_POST['bayar'] ?? 0);
    $metode = in_array($_POST['metode'] ?? '', ['Tunai', 'Debit', 'QRIS'], true) ? $_POST['metode'] : 'Tunai';
    try {
        $id = $trx->simpanTransaksi($_SESSION['kasir']['id'], $keranjang, $bayar, $metode);
        $_SESSION['keranjang'] = [];
        flash('success', 'Transaksi berhasil disimpan.');
        redirect('struk.php?id=' . $id);
    } catch (Exception $ex) {
        flash('danger', $ex->getMessage());     // pesan kekurangan / stok
        redirect('pembayaran.php');
    }
}
$judul = 'Pembayaran';
include 'includes/header.php';
?>
<div class="grid two">
  <div class="card">
    <h2>Rincian Belanja</h2>
    <div class="table-wrap"><table>
      <tr><th>Barang</th><th class="r">Harga</th><th class="r">Qty</th><th class="r">Subtotal</th></tr>
      <?php foreach ($keranjang as $k): ?>
      <tr><td><?= e($k['nama']) ?></td><td class="r"><?= rupiah($k['harga']) ?></td><td class="r"><?= (int)$k['jumlah'] ?></td><td class="r"><?= rupiah(DetailTransaksi::hitungSubtotal($k['harga'], $k['jumlah'])) ?></td></tr>
      <?php endforeach; ?>
      <tr><th colspan="3" class="r">TOTAL</th><th class="r total"><?= rupiah($total) ?></th></tr>
    </table></div>
    <a href="transaksi.php" class="btn sec sm" style="margin-top:.8rem">← Ubah keranjang</a>
  </div>
  <form method="post" class="card sticky">
    <h2>Pembayaran</h2>
    <?= csrf_field() ?>
    <label>Total belanja</label><div class="total"><?= rupiah($total) ?></div>
    <label for="metode">Metode pembayaran</label>
    <select id="metode" name="metode"><option>Tunai</option><option>Debit</option><option>QRIS</option></select>
    <label for="bayar">Uang pembayaran (Rp)</label>
    <input type="number" id="bayar" name="bayar" min="0" step="any" required data-total="<?= $total ?>" autofocus>
    <div id="infoKembalian" class="hint"></div>
    <div class="row" style="margin:.6rem 0">
      <button type="button" class="btn sm sec" data-uang="pas">Uang pas</button>
      <button type="button" class="btn sm sec" data-uang="50000">50rb</button>
      <button type="button" class="btn sm sec" data-uang="100000">100rb</button>
    </div>
    <button class="btn green block">Bayar & Cetak Struk</button>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
