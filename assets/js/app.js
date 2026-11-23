// Menu mobile
document.getElementById('burger')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
});

// Konfirmasi aksi berbahaya
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => { if (!confirm(el.dataset.confirm)) e.preventDefault(); });
});

// Format rupiah
const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

// Hitung kembalian langsung di halaman pembayaran
const bayar = document.getElementById('bayar');
if (bayar) {
  const total = parseFloat(bayar.dataset.total);
  const info = document.getElementById('infoKembalian');
  const update = () => {
    const v = parseFloat(bayar.value) || 0;
    if (v >= total) { info.textContent = 'Kembalian: ' + rp(v - total); info.className = 'hint ok'; }
    else { info.textContent = 'Kurang: ' + rp(total - v); info.className = 'hint bad'; }
  };
  bayar.addEventListener('input', update); update();
  document.querySelectorAll('[data-uang]').forEach(b => b.addEventListener('click', () => {
    bayar.value = b.dataset.uang === 'pas' ? total : b.dataset.uang; update();
  }));
}

// Pilihan periode laporan
const preset = document.getElementById('preset');
if (preset) {
  const fmt = d => d.toISOString().slice(0, 10);
  const loc = d => new Date(d.getTime() - d.getTimezoneOffset() * 60000);
  preset.addEventListener('change', () => {
    const now = new Date(), a = document.getElementById('mulai'), b = document.getElementById('akhir');
    let s = new Date(now), e = new Date(now);
    if (preset.value === 'hari') {}
    else if (preset.value === 'minggu') s.setDate(now.getDate() - 6);
    else if (preset.value === 'bulan') s = new Date(now.getFullYear(), now.getMonth(), 1);
    else return;
    a.value = fmt(loc(s)); b.value = fmt(loc(e));
  });
}
