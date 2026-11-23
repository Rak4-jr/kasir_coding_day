<?php
require 'includes/init.php';
wajib_login();
csrf_check();
$trx = new Transaksi();
$keranjang = $trx->buatTransaksi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id_produk'] ?? 0);
    $jml  = (int)($_POST['jumlah'] ?? 1);
    $q    = trim($_POST['q'] ?? '');

    if ($aksi === 'tambah') {
        $p = Produk::cari($id);
        $ada = $_SESSION['keranjang'][$id]['jumlah'] ?? 0;
        $jml = max(1, $jml);
        if (!$p || $p['stok'] < 1) {
            flash('danger', 'Barang tidak tersedia.');
        } elseif ($p['stok'] < $ada + $jml) {
            flash('danger', 'Stok "' . $p['nama_produk'] . '" tidak cukup (tersisa ' . $p['stok'] . ').');
        } else {
            $_SESSION['keranjang'][$id] = ['id_produk' => $id, 'nama' => $p['nama_produk'], 'harga' => $p['harga'], 'jumlah' => $ada + $jml];
            flash('success', $p['nama_produk'] . ' ditambahkan ke keranjang.');
        }
    } elseif ($aksi === 'ubah' && isset($_SESSION['keranjang'][$id])) {
        $p = Produk::cari($id);
        if ($jml <= 0) {
            unset($_SESSION['keranjang'][$id]);
        } elseif (!$p || $p['stok'] < $jml) {
            flash('danger', 'Stok tidak cukup (tersisa ' . ($p['stok'] ?? 0) . ').');
        } else {
            $_SESSION['keranjang'][$id]['jumlah'] = $jml;
        }
    } elseif ($aksi === 'hapus') {
        unset($_SESSION['keranjang'][$id]);
    } elseif ($aksi === 'kosongkan') {
        $_SESSION['keranjang'] = [];
    } elseif ($aksi === 'lanjut') {
        if (empty($_SESSION['keranjang'])) {                 // memeriksa keranjang
            flash('warning', 'Keranjang masih kosong.');
        } else {
            redirect('pembayaran.php');                     // melanjutkan ke pembayaran
        }
    }
    redirect('transaksi.php' . ($q !== '' ? '?q=' . urlencode($q) : ''));
}

$q = trim($_GET['q'] ?? '');
$daftar = Produk::semua($q);
$keranjang = $_SESSION['keranjang'];
$total = $trx->hitungTotal($keranjang);
$judul = 'Transaksi';
include 'includes/header.php';
?>
<div class="grid two">
  <div class="card">
    <h2>Daftar Barang</h2>
    <form method="get" class="row" style="margin-bottom:.8rem">
      <input name="q" value="<?= e($q) ?>" placeholder="Cari nama / kategori barang...">
      <button class="btn sec">Cari</button>
    </form>
    <div class="table-wrap"><table>
      <tr><th>Barang</th><th class="r">Harga</th><th class="r">Stok</th><th>Tambah</th></tr>
      <?php foreach ($daftar as $p): ?>
      <tr>
        <td><?= e($p['nama_produk']) ?><br><span class="muted" style="font-size:.8rem"><?= e($p['kategori']) ?></span></td>
        <td class="r"><?= rupiah($p['harga']) ?></td>
        <td class="r"><?= $p['stok'] > 0 ? '<span class="badge' . ($p['stok'] <= 10 ? ' low' : '') . '">' . (int)$p['stok'] . '</span>' : '<span class="badge low">Habis</span>' ?></td>
        <td>
          <?php if ($p['stok'] > 0): ?>
          <form method="post" class="inline">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="tambah"><input type="hidden" name="id_produk" value="<?= $p['id_produk'] ?>"><input type="hidden" name="q" value="<?= e($q) ?>">
            <input type="number" name="jumlah" value="1" min="1" max="<?= (int)$p['stok'] ?>">
            <button class="btn sm">+</button>
          </form>
          <?php else: ?><span class="muted">Tidak tersedia</span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; if (!$daftar): ?><tr><td colspan="4" class="muted">Barang tidak ditemukan.</td></tr><?php endif; ?>
    </table></div>
  </div>

  <div class="card sticky">
    <h2>🛒 Keranjang</h2>
    <?php if (!$keranjang): ?>
      <p class="muted">Belum ada barang.</p>
    <?php else: ?>
      <?php foreach ($keranjang as $k): ?>
        <div style="border-bottom:1px solid var(--bd);padding:.5rem 0">
          <strong><?= e($k['nama']) ?></strong>
          <div class="muted" style="font-size:.85rem"><?= rupiah($k['harga']) ?> × <?= (int)$k['jumlah'] ?> = <?= rupiah(DetailTransaksi::hitungSubtotal($k['harga'], $k['jumlah'])) ?></div>
          <div class="row" style="margin-top:.3rem">
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="aksi" value="ubah"><input type="hidden" name="id_produk" value="<?= $k['id_produk'] ?>">
              <input type="number" name="jumlah" value="<?= (int)$k['jumlah'] ?>" min="0">
              <button class="btn sm sec">Ubah</button>
            </form>
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_produk" value="<?= $k['id_produk'] ?>">
              <button class="btn sm red">Hapus</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
      <p style="margin:1rem 0 .2rem" class="muted">Total belanja</p>
      <div class="total"><?= rupiah($total) ?></div>
    <?php endif; ?>
    <form method="post" class="row" style="margin-top:1rem">
      <?= csrf_field() ?>
      <button name="aksi" value="lanjut" class="btn green" style="flex:1">Lanjut Pembayaran</button>
      <?php if ($keranjang): ?><button name="aksi" value="kosongkan" class="btn sec" data-confirm="Kosongkan keranjang?">Kosongkan</button><?php endif; ?>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
