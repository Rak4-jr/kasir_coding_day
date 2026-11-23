<?php
require 'includes/init.php';
wajib_login();
$mulai = $_GET['mulai'] ?? ''; $akhir = $_GET['akhir'] ?? '';
$lap = null;
if ($mulai !== '' && $akhir !== '') {
    if ($mulai > $akhir) { flash('danger', 'Tanggal mulai tidak boleh setelah tanggal akhir.'); redirect('laporan.php'); }
    $lap = (new Laporan())->lihatLaporan($mulai, $akhir, $_SESSION['kasir']['id']);
}
$judul = 'Laporan Penjualan';
include 'includes/header.php';
?>
<form class="card filters" method="get">
  <div><label>Periode</label>
    <select id="preset"><option value="">Pilih periode...</option><option value="hari">Hari ini</option><option value="minggu">7 hari terakhir</option><option value="bulan">Bulan ini</option><option value="custom">Kustom</option></select></div>
  <div><label>Tanggal mulai</label><input type="date" id="mulai" name="mulai" required value="<?= e($mulai) ?>"></div>
  <div><label>Tanggal akhir</label><input type="date" id="akhir" name="akhir" required value="<?= e($akhir) ?>"></div>
  <div><button class="btn">Tampilkan Laporan</button></div>
</form>
<?php if ($lap): ?>
<div class="grid stats">
  <div class="card stat"><div class="l">Total penjualan</div><div class="n"><?= rupiah($lap['total']) ?></div></div>
  <div class="card stat"><div class="l">Jumlah transaksi</div><div class="n"><?= count($lap['rows']) ?></div></div>
  <div class="card stat"><div class="l">Periode</div><div class="n" style="font-size:1rem"><?= e(date('d/m/Y', strtotime($mulai))) ?> – <?= e(date('d/m/Y', strtotime($akhir))) ?></div></div>
</div>
<div class="grid two">
  <div class="card"><h2>Transaksi</h2><div class="table-wrap"><table>
    <tr><th>No</th><th>Tanggal</th><th>Kasir</th><th class="r">Total</th></tr>
    <?php foreach ($lap['rows'] as $t): ?>
      <tr><td><a href="riwayat.php?id=<?= $t['id_transaksi'] ?>">#<?= $t['id_transaksi'] ?></a></td><td><?= e(date('d/m/Y H:i', strtotime($t['tanggal']))) ?></td><td><?= e($t['nama_kasir']) ?></td><td class="r"><?= rupiah($t['total']) ?></td></tr>
    <?php endforeach; if (!$lap['rows']): ?><tr><td colspan="4" class="muted">Tidak ada penjualan pada periode ini.</td></tr><?php endif; ?>
    <tr><th colspan="3" class="r">Total</th><th class="r"><?= rupiah($lap['total']) ?></th></tr>
  </table></div></div>
  <div class="card"><h2>Produk terlaris</h2><div class="table-wrap"><table>
    <tr><th>Produk</th><th class="r">Terjual</th><th class="r">Omzet</th></tr>
    <?php foreach ($lap['top'] as $p): ?><tr><td><?= e($p['nama_produk']) ?></td><td class="r"><?= (int)$p['terjual'] ?></td><td class="r"><?= rupiah($p['omzet']) ?></td></tr><?php endforeach; ?>
    <?php if (!$lap['top']): ?><tr><td colspan="3" class="muted">-</td></tr><?php endif; ?>
  </table></div>
  <button class="btn sec sm noprint" onclick="window.print()" style="margin-top:.8rem">🖨️ Cetak laporan</button></div>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
