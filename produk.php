<?php
require 'includes/init.php';
wajib_login();
csrf_check();
$produk = new Produk();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id = (int)($_POST['id_produk'] ?? 0);
    try {
        if ($aksi === 'simpan') {
            $nama = trim($_POST['nama_produk'] ?? ''); $kat = trim($_POST['kategori'] ?? '');
            $harga = (float)($_POST['harga'] ?? 0); $stok = (int)($_POST['stok'] ?? 0);
            if ($nama === '' || $kat === '' || $harga <= 0 || $stok < 0) {
                flash('danger', 'Data produk tidak valid. Periksa nama, kategori, harga (>0) dan stok (≥0).');
            } elseif ($id) {
                $produk->ubahProduk($id, $nama, $kat, $harga, $stok);
                flash('success', 'Perubahan produk disimpan.');
            } else {
                $hasil = $produk->tambahProduk($nama, $kat, $harga, $stok);
                flash('success', $hasil === 'stok' ? 'Produk sudah ada, stok berhasil ditambahkan.' : 'Produk baru berhasil disimpan.');
            }
        } elseif ($aksi === 'stok') {
            $n = max(1, (int)($_POST['delta'] ?? 1));
            $delta = ($_POST['arah'] ?? '') === 'kurang' ? -$n : $n;
            if ($produk->updateStok($id, $delta)) flash('success', 'Stok diperbarui.');
            else flash('danger', 'Stok tidak boleh kurang dari 0.');
        } elseif ($aksi === 'hapus') {
            $produk->hapusProduk($id);
            flash('success', 'Produk dihapus.');
        }
    } catch (mysqli_sql_exception $ex) {
        flash('danger', 'Produk tidak bisa dihapus karena sudah dipakai di transaksi. Kurangi stok menjadi 0 saja.');
    }
    redirect('produk.php');
}

$edit = isset($_GET['edit']) ? Produk::cari((int)$_GET['edit']) : null;
$q = trim($_GET['q'] ?? '');
$daftar = Produk::semua($q);
$judul = 'Kelola Produk';
include 'includes/header.php';
?>
<div class="grid two">
  <div class="card">
    <h2>Daftar Produk</h2>
    <form method="get" class="row" style="margin-bottom:.8rem">
      <input name="q" value="<?= e($q) ?>" placeholder="Cari produk...">
      <button class="btn sec">Cari</button>
    </form>
    <div class="table-wrap"><table>
      <tr><th>Produk</th><th class="r">Harga</th><th class="r">Stok</th><th>Atur stok</th><th></th></tr>
      <?php foreach ($daftar as $p): ?>
      <tr>
        <td><?= e($p['nama_produk']) ?><br><span class="muted" style="font-size:.8rem"><?= e($p['kategori']) ?></span></td>
        <td class="r"><?= rupiah($p['harga']) ?></td>
        <td class="r"><span class="badge<?= $p['stok'] <= 10 ? ' low' : '' ?>"><?= (int)$p['stok'] ?></span></td>
        <td>
          <form method="post" class="inline">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="stok"><input type="hidden" name="id_produk" value="<?= $p['id_produk'] ?>">
            <input type="number" name="delta" value="1" min="1">
            <button class="btn sm green" name="arah" value="tambah">+</button>
            <button class="btn sm sec" name="arah" value="kurang">−</button>
          </form>
        </td>
        <td>
          <div class="row">
            <a class="btn sm sec" href="produk.php?edit=<?= $p['id_produk'] ?>">Ubah</a>
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_produk" value="<?= $p['id_produk'] ?>">
              <button class="btn sm red" data-confirm="Hapus produk ini?">Hapus</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; if (!$daftar): ?><tr><td colspan="5" class="muted">Produk tidak ditemukan.</td></tr><?php endif; ?>
    </table></div>
  </div>

  <form method="post" class="card sticky">
    <h2><?= $edit ? 'Ubah Produk' : 'Tambah Produk' ?></h2>
    <?= csrf_field() ?><input type="hidden" name="aksi" value="simpan"><input type="hidden" name="id_produk" value="<?= $edit['id_produk'] ?? 0 ?>">
    <label>Nama produk</label><input name="nama_produk" required value="<?= e($edit['nama_produk'] ?? '') ?>">
    <label>Kategori</label><input name="kategori" required list="kat" value="<?= e($edit['kategori'] ?? '') ?>">
    <datalist id="kat"><option>Sembako</option><option>Makanan</option><option>Minuman</option><option>Kebersihan</option></datalist>
    <label>Harga (Rp)</label><input type="number" name="harga" min="1" step="any" required value="<?= e($edit['harga'] ?? '') ?>">
    <label>Stok</label><input type="number" name="stok" min="0" required value="<?= e($edit['stok'] ?? '') ?>">
    <?php if (!$edit): ?><p class="muted" style="font-size:.8rem">Jika nama produk sudah ada, stok akan ditambahkan ke produk tersebut.</p><?php endif; ?>
    <div class="row" style="margin-top:.8rem">
      <button class="btn">Simpan</button>
      <?php if ($edit): ?><a class="btn sec" href="produk.php">Batal</a><?php endif; ?>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
