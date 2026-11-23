<?php
/**
 * Kelas-kelas sesuai Class Diagram:
 * Kasir, Produk, Transaksi, DetailTransaksi, Pembayaran, Struk, Laporan
 */

class Kasir {
    public $id, $nama, $username, $password;

    public function login($username, $password) {
        global $db;
        $s = $db->prepare("SELECT * FROM kasir WHERE username = ?");
        $s->bind_param('s', $username);
        $s->execute();
        $r = $s->get_result()->fetch_assoc();
        if ($r && password_verify($password, $r['password'])) {
            session_regenerate_id(true);
            $_SESSION['kasir'] = ['id' => (int)$r['id'], 'nama' => $r['nama'], 'username' => $r['username']];
            return true;
        }
        return false;
    }

    public function logout() {
        $_SESSION = [];
        session_destroy();
    }
}

class Produk {
    public $id_produk, $nama_produk, $kategori, $harga, $stok;

    public static function semua($q = '') {
        global $db;
        $like = '%' . $q . '%';
        $s = $db->prepare("SELECT * FROM produk WHERE nama_produk LIKE ? OR kategori LIKE ? ORDER BY nama_produk");
        $s->bind_param('ss', $like, $like);
        $s->execute();
        return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function cari($id) {
        global $db;
        $s = $db->prepare("SELECT * FROM produk WHERE id_produk = ?");
        $s->bind_param('i', $id);
        $s->execute();
        return $s->get_result()->fetch_assoc();
    }

    /** Jika produk (nama sama) sudah ada -> stok ditambah. Jika belum -> disimpan sebagai produk baru. */
    public function tambahProduk($nama, $kategori, $harga, $stok) {
        global $db;
        $s = $db->prepare("SELECT id_produk FROM produk WHERE LOWER(nama_produk) = LOWER(?)");
        $s->bind_param('s', $nama);
        $s->execute();
        $ada = $s->get_result()->fetch_assoc();
        if ($ada) {
            $this->updateStok((int)$ada['id_produk'], $stok);
            return 'stok';
        }
        $i = $db->prepare("INSERT INTO produk (nama_produk, kategori, harga, stok) VALUES (?,?,?,?)");
        $i->bind_param('ssdi', $nama, $kategori, $harga, $stok);
        $i->execute();
        return 'baru';
    }

    public function ubahProduk($id, $nama, $kategori, $harga, $stok) {
        global $db;
        $s = $db->prepare("UPDATE produk SET nama_produk=?, kategori=?, harga=?, stok=? WHERE id_produk=?");
        $s->bind_param('ssdii', $nama, $kategori, $harga, $stok, $id);
        $s->execute();
        return true;
    }

    /** $delta positif = tambah stok, negatif = kurangi stok (tidak boleh di bawah 0) */
    public function updateStok($id, $delta) {
        global $db;
        $s = $db->prepare("UPDATE produk SET stok = stok + ? WHERE id_produk = ? AND stok + ? >= 0");
        $s->bind_param('iii', $delta, $id, $delta);
        $s->execute();
        return $s->affected_rows > 0;
    }

    public function hapusProduk($id) {
        global $db;
        $s = $db->prepare("DELETE FROM produk WHERE id_produk = ?");
        $s->bind_param('i', $id);
        $s->execute();
        return $s->affected_rows > 0;
    }
}

class DetailTransaksi {
    public $id_detail, $jumlah, $harga, $subtotal;

    public static function hitungSubtotal($harga, $jumlah) {
        return (float)$harga * (int)$jumlah;
    }

    public static function daftar($idTransaksi) {
        global $db;
        $s = $db->prepare("SELECT d.*, p.nama_produk FROM detail_transaksi d JOIN produk p ON p.id_produk = d.id_produk WHERE d.id_transaksi = ?");
        $s->bind_param('i', $idTransaksi);
        $s->execute();
        return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

class Pembayaran {
    public $id_pembayaran, $jumlah_bayar, $kembalian, $metode_pembayaran;

    /** true bila uang pembayaran cukup */
    public function prosesPembayaran($total, $bayar) {
        return (float)$bayar >= (float)$total;
    }

    public function hitungKembalian($total, $bayar) {
        return max(0, (float)$bayar - (float)$total);
    }

    public function simpan($idTransaksi, $bayar, $kembalian, $metode) {
        global $db;
        $s = $db->prepare("INSERT INTO pembayaran (id_transaksi, jumlah_bayar, kembalian, metode_pembayaran) VALUES (?,?,?,?)");
        $s->bind_param('idds', $idTransaksi, $bayar, $kembalian, $metode);
        $s->execute();
        return $db->insert_id;
    }
}

class Struk {
    public $id_struk, $tanggal_cetak;

    public function cetakStruk($idPembayaran) {
        global $db;
        $s = $db->prepare("INSERT INTO struk (id_pembayaran, tanggal_cetak) VALUES (?, NOW())");
        $s->bind_param('i', $idPembayaran);
        $s->execute();
        return $db->insert_id;
    }

    public static function ambil($idTransaksi) {
        global $db;
        $s = $db->prepare("SELECT t.*, k.nama AS nama_kasir, p.metode_pembayaran, p.id_pembayaran,
                (SELECT MAX(tanggal_cetak) FROM struk WHERE id_pembayaran = p.id_pembayaran) AS tanggal_cetak
            FROM transaksi t
            JOIN kasir k ON k.id = t.id_kasir
            LEFT JOIN pembayaran p ON p.id_transaksi = t.id_transaksi
            WHERE t.id_transaksi = ?");
        $s->bind_param('i', $idTransaksi);
        $s->execute();
        return $s->get_result()->fetch_assoc();
    }
}

class Transaksi {
    public $id_transaksi, $tanggal, $total, $bayar, $kembalian;

    /** Membuat/mengambil keranjang transaksi aktif (disimpan di session) */
    public function buatTransaksi() {
        if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];
        return $_SESSION['keranjang'];
    }

    public function hitungTotal(array $keranjang) {
        $total = 0;
        foreach ($keranjang as $k) $total += DetailTransaksi::hitungSubtotal($k['harga'], $k['jumlah']);
        return $total;
    }

    /** Menyimpan transaksi + detail + pembayaran + struk dalam satu database transaction */
    public function simpanTransaksi($idKasir, array $keranjang, $bayar, $metode) {
        global $db;
        $db->begin_transaction();
        try {
            $total = 0;
            $baris = [];
            foreach ($keranjang as $k) {
                $s = $db->prepare("SELECT nama_produk, harga, stok FROM produk WHERE id_produk = ? FOR UPDATE");
                $s->bind_param('i', $k['id_produk']);
                $s->execute();
                $p = $s->get_result()->fetch_assoc();
                if (!$p || $p['stok'] < $k['jumlah']) {
                    throw new Exception('Stok "' . ($p['nama_produk'] ?? 'produk') . '" tidak mencukupi.');
                }
                $sub = DetailTransaksi::hitungSubtotal($p['harga'], $k['jumlah']);
                $total += $sub;
                $baris[] = [(int)$k['id_produk'], (int)$k['jumlah'], (float)$p['harga'], $sub];
            }

            $pay = new Pembayaran();
            if (!$pay->prosesPembayaran($total, $bayar)) {
                throw new Exception('Pembayaran kurang ' . rupiah($total - $bayar) . '. Total belanja ' . rupiah($total) . '.');
            }
            $kembalian = $pay->hitungKembalian($total, $bayar);

            $t = $db->prepare("INSERT INTO transaksi (id_kasir, tanggal, total, bayar, kembalian) VALUES (?, NOW(), ?, ?, ?)");
            $t->bind_param('iddd', $idKasir, $total, $bayar, $kembalian);
            $t->execute();
            $idT = $db->insert_id;

            foreach ($baris as [$idP, $jml, $harga, $sub]) {
                $d = $db->prepare("INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga, subtotal) VALUES (?,?,?,?,?)");
                $d->bind_param('iiidd', $idT, $idP, $jml, $harga, $sub);
                $d->execute();
                $u = $db->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
                $u->bind_param('ii', $jml, $idP);
                $u->execute();
            }

            $idPay = $pay->simpan($idT, $bayar, $kembalian, $metode);
            (new Struk())->cetakStruk($idPay);

            $db->commit();
            return $idT;
        } catch (Throwable $e) {
            $db->rollback();
            throw $e;
        }
    }

    public static function riwayat($dari = '', $sampai = '') {
        global $db;
        $sql = "SELECT t.*, k.nama AS nama_kasir FROM transaksi t JOIN kasir k ON k.id = t.id_kasir WHERE 1=1";
        $types = ''; $par = [];
        if ($dari !== '')   { $sql .= " AND DATE(t.tanggal) >= ?"; $types .= 's'; $par[] = $dari; }
        if ($sampai !== '') { $sql .= " AND DATE(t.tanggal) <= ?"; $types .= 's'; $par[] = $sampai; }
        $sql .= " ORDER BY t.tanggal DESC LIMIT 200";
        $s = $db->prepare($sql);
        if ($par) $s->bind_param($types, ...$par);
        $s->execute();
        return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

class Laporan {
    public $id_laporan, $tanggal_mulai, $tanggal_akhir, $total_penjualan;

    /** Mengambil data penjualan pada periode, menghitung total, dan mencatat laporan */
    public function lihatLaporan($mulai, $akhir, $idKasir) {
        global $db;
        $s = $db->prepare("SELECT t.*, k.nama AS nama_kasir FROM transaksi t JOIN kasir k ON k.id = t.id_kasir
                           WHERE DATE(t.tanggal) BETWEEN ? AND ? ORDER BY t.tanggal");
        $s->bind_param('ss', $mulai, $akhir);
        $s->execute();
        $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $total = array_sum(array_column($rows, 'total'));

        $i = $db->prepare("INSERT INTO laporan (id_kasir, tanggal_mulai, tanggal_akhir, total_penjualan) VALUES (?,?,?,?)");
        $i->bind_param('issd', $idKasir, $mulai, $akhir, $total);
        $i->execute();

        $q = $db->prepare("SELECT p.nama_produk, SUM(d.jumlah) AS terjual, SUM(d.subtotal) AS omzet
            FROM detail_transaksi d
            JOIN transaksi t ON t.id_transaksi = d.id_transaksi
            JOIN produk p ON p.id_produk = d.id_produk
            WHERE DATE(t.tanggal) BETWEEN ? AND ?
            GROUP BY p.id_produk, p.nama_produk ORDER BY terjual DESC LIMIT 5");
        $q->bind_param('ss', $mulai, $akhir);
        $q->execute();
        $top = $q->get_result()->fetch_all(MYSQLI_ASSOC);

        return ['rows' => $rows, 'total' => $total, 'top' => $top];
    }
}
