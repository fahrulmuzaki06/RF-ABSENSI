<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/swal.php';
require_once __DIR__ . '/../models/Jadwal.php';

$user     = cek_role('pegawai');
$uid      = (int)$user['id'];
$sekarang = new DateTime('now');
$hariIni  = $sekarang->format('Y-m-d');

$senin = clone $sekarang;
$senin->modify('this week monday');
$minggu = clone $senin;
$minggu->modify('+6 days');

$tglMulai   = $senin->format('Y-m-d');
$tglSelesai = $minggu->format('Y-m-d');

$daftarJadwal  = jadwal_mingguan($pdo, $uid, $tglMulai, $tglSelesai);
$judul_halaman = 'Jadwal Shift';
$menu_aktif    = 'jadwal';

require_once __DIR__ . '/../includes/Header.php';
?>

<header class="topbar">
    <div>
        <h1>Jadwal Kerja Anda</h1>
        <p>Kalender shift penugasan mingguan (View Only)</p>
    </div>
    <div class="chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Hari Ini: <?= e(tanggal_indo($hariIni)) ?>
    </div>
</header>

<div class="content">
    <section class="card penuh">
        <h2>Jadwal Minggu Ini: <?= e(tanggal_indo($tglMulai)) ?> - <?= e(tanggal_indo($tglSelesai)) ?></h2>

        <div class="schedule-grid">
            <?php foreach ($daftarJadwal as $j): ?>
                <?php 
                    $isToday = ($j['tanggal'] === $hariIni);
                    $isLibur = ($j['status'] === 'libur' || empty($j['nama_shift']));
                ?>
                <div class="schedule-card <?= $isToday ? 'active' : '' ?> <?= $isLibur ? 'off' : '' ?>">
                    <div class="day-header">
                        <span class="day-name"><?= e(nama_hari($j['tanggal'])) ?></span>
                        <span class="day-date"><?= e(format_tgl_singkat($j['tanggal'])) ?></span>
                    </div>

                    <div class="shift-info">
                        <?php if ($isLibur): ?>
                            <div class="shift-title libur">LIBUR (Off)</div>
                            <div class="shift-time">-</div>
                            <span class="tag-posisi"><?= e($j['posisi'] ?? 'Istirahat Mingguan') ?></span>
                        <?php else: ?>
                            <div class="shift-title"><?= e($j['nama_shift']) ?></div>
                            <div class="shift-time">
                                <?= e(substr($j['jam_mulai'], 0, 5)) ?> - <?= e(substr($j['jam_selesai'], 0, 5)) ?>
                            </div>
                            <span class="tag-posisi"><?= e($j['posisi'] ?? 'Tugas Utama') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/Footer.php'; ?>