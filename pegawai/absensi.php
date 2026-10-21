<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/swal.php';
require_once __DIR__ . '/../models/Absensi.php';

$user     = cek_role('pegawai');
$uid      = (int)$user['id'];
$sekarang = new DateTime('now');
$hariIni  = $sekarang->format('Y-m-d');

$jadwal  = jadwal_hari_ini($pdo, $uid, $hariIni);
$absen   = $jadwal ? absensi_by_jadwal($pdo, (int)$jadwal['id']) : null;
$tombol  = status_tombol($jadwal, $absen, $sekarang);
$rekap   = rekap_bulan($pdo, $uid, $sekarang->format('Y-m-01'), $hariIni);
$riwayat = riwayat_absensi($pdo, $uid, 5);

$persen = $rekap['hari_kerja'] > 0 ? round($rekap['hadir'] / $rekap['hari_kerja'] * 100, 1) : 0;

$kelasMasuk  = $tombol['sudah_masuk']  ? 'selesai' : ($tombol['bisa_masuk']  ? 'siap' : '');
$kelasPulang = $tombol['sudah_pulang'] ? 'selesai' : ($tombol['bisa_pulang'] ? 'siap' : '');

$judul_halaman = 'Absensi Pegawai';
$menu_aktif    = 'absensi';
require_once __DIR__ . '/../includes/Header.php';
?>

<header class="topbar">
    <div>
        <h1>Sesi Absensi</h1>
        <p>Lakukan pencatatan masuk dan keluar kerja</p>
    </div>
    <div class="chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Hari Ini: <?= e(tanggal_indo($hariIni)) ?>
    </div>
</header>

<div class="content">
    <section class="card">
        <h2>Waktu Server Aktif</h2>
        <div class="clock-box">
            <div class="clock" id="jam" aria-live="off">--:--:--</div>
            <small>Waktu Indonesia Barat (WIB)</small>
        </div>

        <div class="actions">
            <form method="POST" action="clock_in.php">
                <button type="submit" class="act <?= $kelasMasuk ?>" <?= $tombol['bisa_masuk'] ? '' : 'disabled' ?>>
                    <svg viewBox="0 0 24 24"><path d="m5 12 5 5L20 7"/></svg>
                    <span class="act-title">CLOCK IN</span>
                    <span class="act-sub"><?= e($tombol['info_masuk']) ?></span>
                </button>
            </form>
            <form method="POST" action="clock_out.php">
                <button type="submit" class="act <?= $kelasPulang ?>" <?= $tombol['bisa_pulang'] ? '' : 'disabled' ?>>
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    <span class="act-title">CLOCK OUT</span>
                    <span class="act-sub"><?= e($tombol['info_pulang']) ?></span>
                </button>
            </form>
        </div>

        <?php if ($jadwal && $jadwal['status'] === 'terjadwal'): ?>
            <p class="catatan"><?= e($jadwal['nama_shift']) ?>: <?= e(substr($jadwal['jam_mulai'], 0, 5)) ?> - <?= e(substr($jadwal['jam_selesai'], 0, 5)) ?></p>
        <?php elseif ($tombol['catatan']): ?>
            <p class="catatan"><?= e($tombol['catatan']) ?></p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Kehadiran Bulan Ini</h2>
        <dl class="stats">
            <div><dt>Persentase Kehadiran</dt><dd><?= number_format($persen, 1, '.', '') ?>%</dd></div>
            <div><dt>Jumlah Hari Kerja</dt><dd><?= $rekap['hari_kerja'] ?> Hari</dd></div>
            <div><dt>Kehadiran Tepat Waktu</dt><dd><?= $rekap['tepat_waktu'] ?> Hari</dd></div>
            <div><dt>Akumulasi Keterlambatan</dt><dd><?= $rekap['terlambat'] ?> Kali</dd></div>
        </dl>
    </section>

    <section class="card penuh">
        <h2>Riwayat Kehadiran Terakhir</h2>
        <div class="table-wrap">
            <table class="riwayat">
                <thead>
                    <tr><th>Tanggal</th><th>Shift</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status Kehadiran</th></tr>
                </thead>
                <tbody>
                <?php if (!$riwayat): ?>
                    <tr><td class="kosong" colspan="5">Belum ada riwayat absensi.</td></tr>
                <?php endif; ?>
                <?php foreach ($riwayat as $r): ?>
                    <?php $telat = $r['status_kehadiran'] === 'terlambat'; ?>
                    <tr>
                        <td><?= e(tanggal_indo($r['tanggal'])) ?></td>
                        <td><?= e($r['nama_shift']) ?></td>
                        <td><?= e(format_jam($r['check_in'])) ?></td>
                        <td><?= e(format_jam($r['check_out'])) ?></td>
                        <td class="status <?= $telat ? 'telat' : '' ?>"><?= $telat ? 'Terlambat' : 'Hadir' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php show_flash_swal(); ?>

<script>
(function () {
    var el = document.getElementById('jam');
    var selisih = <?= $sekarang->getTimestamp() * 1000 ?> - Date.now();
    var reloadAt = <?= $tombol['reload_ts'] ? (int)$tombol['reload_ts'] * 1000 : 'null' ?>;

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        var sekarang = Date.now() + selisih;
        var d = new Date(sekarang);
        var wib = new Date(d.getTime() + d.getTimezoneOffset() * 60000 + 7 * 3600000);
        el.textContent = pad(wib.getHours()) + ':' + pad(wib.getMinutes()) + ':' + pad(wib.getSeconds());
        if (reloadAt && sekarang >= reloadAt) { location.reload(); }
    }
    tick();
    setInterval(tick, 1000);
})();
</script>

<?php require_once __DIR__ . '/../includes/Footer.php'; ?>