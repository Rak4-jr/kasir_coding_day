<?php
require 'includes/init.php';
wajib_login();
csrf_check();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new Kasir())->logout();                 // mengakhiri sesi login
    session_start();
    flash('success', 'Anda telah logout.');
    redirect('login.php');                   // menampilkan halaman login
}
$judul = 'Logout';
include 'includes/header.php';
?>
<div class="card" style="max-width:420px">
  <h2>Yakin logout?</h2>
  <p class="muted">Sesi login Anda akan diakhiri.</p>
  <form method="post" class="row">
    <?= csrf_field() ?>
    <button class="btn red">Ya, logout</button>
    <a class="btn sec" href="index.php">Tidak</a>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
