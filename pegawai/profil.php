<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/swal.php';
require_once __DIR__ . '/../models/Pegawai.php';

$user     = cek_role('pegawai');
$uid      = (int)$user['id'];
$sekarang = new DateTime('now');
$hariIni  = $sekarang->format('Y-m-d');

$pegawai = detail_pegawai($pdo, $uid) ?? $user;

$judul_halaman = 'Profil Saya';
$menu_aktif    = 'profil';

require_once __DIR__ . '/../includes/Header.php';
?>

<header class="topbar">
    <div>
        <h1>Profil Saya</h1>
        <p>Informasi akun dan pengajuan izin karyawan</p>
    </div>
    <div class="chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Hari Ini: <?= e(tanggal_indo($hariIni)) ?>
    </div>
</header>

<div class="content">
    <section class="card">
        <h2>Data Pribadi Pegawai</h2>
        
        <div class="profile-header">
            <div class="avatar-box">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="profile-title">
                <h3><?= e($pegawai['nama_lengkap'] ?? 'Budi Santoso') ?></h3>
                <p class="nip">NIP: <?= e($pegawai['nip'] ?? '1994020384') ?></p>
            </div>
        </div>

        <dl class="profile-info">
            <div class="info-row">
                <dt>Jabatan</dt>
                <dd><?= e($pegawai['jabatan'] ?? 'Cook / Kitchen Staff') ?></dd>
            </div>
            <div class="info-row">
                <dt>Outlet Penugasan</dt>
                <dd><?= e($pegawai['nama_outlet'] ?? 'Richeese Factory - Bandung Kidul') ?></dd>
            </div>
            <div class="info-row">
                <dt>Email Kerja</dt>
                <dd><?= e($pegawai['email'] ?? 'budi.santoso@richeese.com') ?></dd>
            </div>
            <div class="info-row">
                <dt>Nomor Telepon</dt>
                <dd><?= e($pegawai['no_hp'] ?? '0812-3456-7890') ?></dd>
            </div>
            <div class="info-row">
                <dt>Tanggal Bergabung</dt>
                <dd><?= e(tanggal_indo($pegawai['tgl_bergabung'] ?? '2021-01-12')) ?></dd>
            </div>
        </dl>
    </section>

    <section class="card">
        <h2>Formulir Pengajuan Izin / Cuti</h2>
        
        <form method="POST" action="pengajuan_simpan.php" class="form-pengajuan">
            <div class="form-group">
                <label for="tipe_pengajuan">Tipe Pengajuan</label>
                <select id="tipe_pengajuan" name="tipe_pengajuan" required>
                    <option value="Izin Sakit">Izin Sakit</option>
                    <option value="Cuti Tahunan">Cuti Tahunan</option>
                    <option value="Izin Keperluan Pribadi">Izin Keperluan Pribadi</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tgl_mulai">Tanggal Mulai s/d Selesai</label>
                <div class="date-range">
                    <input type="date" id="tgl_mulai" name="tgl_mulai" required>
                    <span>s/d</span>
                    <input type="date" id="tgl_selesai" name="tgl_selesai" required>
                </div>
            </div>

            <div class="form-group">
                <label for="alasan">Alasan Detail</label>
                <textarea id="alasan" name="alasan" rows="4" placeholder="Masukkan keterangan medis atau keperluan mendesak..." required></textarea>
            </div>

            <button type="submit" class="btn-primary">Ajukan Permohonan</button>
        </form>
    </section>
</div>

<?php show_flash_swal(); ?>
<?php require_once __DIR__ . '/../includes/Footer.php'; ?>