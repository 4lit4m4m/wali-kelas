<?php
session_start();
require_once 'config/database.php';

// Proses Login
$error_login = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        header('Location: index.php');
        exit;
    } else {
        $error_login = 'Username atau password salah!';
    }
}

// Proses Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Jika belum login, tampilkan halaman Login
if (!isset($_SESSION['user_id'])):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Informasi Wali Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="card login-card p-4 bg-white">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark m-0">WALI KELAS 10 AR RAHMAN</h4>
            <small class="text-muted">SMA Al Muslim • Ustad Ali Tamam</small>
        </div>
        <?php if (!empty($error_login)): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error_login) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Username</label>
                <input type="text" name="username" class="form-control" required value="ustad.ali">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required value="admin123">
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100 fw-bold py-2" style="background:#0f766e; border:none;">Login Masuk</button>
        </form>
    </div>
</body>
</html>
<?php 
exit;
endif; 

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$success_msg = '';
$error_msg = '';

// Proses Simpan / Edit Perkembangan Siswa
if ($page == 'perkembangan') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_perkembangan'])) {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $tanggal = $_POST['tanggal'];
        $siswa_id = $_POST['siswa_id'];
        $perkembangan = trim($_POST['perkembangan']);
        $kendala = trim($_POST['kendala']);
        $tindak_lanjut = trim($_POST['tindak_lanjut']);
        $catatan_wali = trim($_POST['catatan_wali']);

        if (!empty($tanggal) && !empty($siswa_id) && !empty($perkembangan)) {
            if (!empty($id)) {
                $stmt = $pdo->prepare("UPDATE perkembangan_siswa SET tanggal = ?, siswa_id = ?, perkembangan = ?, kendala = ?, tindak_lanjut = ?, catatan_wali = ? WHERE id = ?");
                $stmt->execute([$tanggal, $siswa_id, $perkembangan, $kendala, $tindak_lanjut, $catatan_wali, $id]);
                $success_msg = "Catatan perkembangan berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO perkembangan_siswa (tanggal, siswa_id, perkembangan, kendala, tindak_lanjut, catatan_wali) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$tanggal, $siswa_id, $perkembangan, $kendala, $tindak_lanjut, $catatan_wali]);
                $success_msg = "Catatan perkembangan berhasil ditambahkan!";
            }
        } else {
            $error_msg = "Tanggal, nama siswa, dan catatan perkembangan wajib diisi!";
        }
    }

    // Proses Hapus Perkembangan
    if (isset($_GET['hapus_perkembangan'])) {
        $id_hapus = $_GET['hapus_perkembangan'];
        $stmt = $pdo->prepare("DELETE FROM perkembangan_siswa WHERE id = ?");
        $stmt->execute([$id_hapus]);
        header('Location: index.php?page=perkembangan');
        exit;
    }
}

// Proses Penilaian Surat & Doa
if ($page == 'hafalan_surat' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_surat'])) {
    $siswa_id = $_POST['siswa_id'];
    $target_surat_id = $_POST['target_surat_id'];
    $status = $_POST['status'];
    $nilai = intval($_POST['nilai']);
    $tanggal = $_POST['tanggal'] ?: date('Y-m-d');
    $catatan = trim($_POST['catatan']);

    $stmt = $pdo->prepare("INSERT INTO penilaian_surat (siswa_id, target_surat_id, status, nilai, tanggal_setoran, catatan) 
                           VALUES (?, ?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE status = VALUES(status), nilai = VALUES(nilai), tanggal_setoran = VALUES(tanggal_setoran), catatan = VALUES(catatan)");
    $stmt->execute([$siswa_id, $target_surat_id, $status, $nilai, $tanggal, $catatan]);
    $success_msg = "Penilaian hafalan surat berhasil disimpan!";
}

if ($page == 'hafalan_doa' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_doa'])) {
    $siswa_id = $_POST['siswa_id'];
    $target_doa_id = $_POST['target_doa_id'];
    $status = $_POST['status'];
    $nilai = intval($_POST['nilai']);
    $tanggal = $_POST['tanggal'] ?: date('Y-m-d');
    $catatan = trim($_POST['catatan']);

    $stmt = $pdo->prepare("INSERT INTO penilaian_doa (siswa_id, target_doa_id, status, nilai, tanggal_setoran, catatan) 
                           VALUES (?, ?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE status = VALUES(status), nilai = VALUES(nilai), tanggal_setoran = VALUES(tanggal_setoran), catatan = VALUES(catatan)");
    $stmt->execute([$siswa_id, $target_doa_id, $status, $nilai, $tanggal, $catatan]);
    $success_msg = "Penilaian hafalan doa berhasil disimpan!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Wali Kelas 10 Ar Rahman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --primary-color: #0f766e; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; }
        #sidebar { width: var(--sidebar-width); position: fixed; top: 0; left: 0; height: 100vh; background: #1e293b; color: #fff; z-index: 1000; overflow-y: auto; }
        #sidebar .sidebar-header { padding: 20px; background: #0f766e; text-align: center; }
        #sidebar ul.components { padding: 20px 0; }
        #sidebar ul li a { padding: 12px 20px; font-size: 0.95rem; display: block; color: #94a3b8; text-decoration: none; border-left: 4px solid transparent; }
        #sidebar ul li a:hover, #sidebar ul li.active a { color: #fff; background: #334155; border-left-color: #14b8a6; }
        #sidebar ul li a i { margin-right: 10px; width: 20px; text-align: center; }
        #content { margin-left: var(--sidebar-width); padding: 30px; }
        .navbar-custom { background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 15px 30px; margin-bottom: 30px; border-radius: 8px; }
        .card-stat { border: none; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        @media print {
            #sidebar, .navbar-custom, .btn, .d-print-none { display: none !important; }
            #content { margin-left: 0 !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>
    <nav id="sidebar" class="d-print-none">
        <div class="sidebar-header">
            <h5 class="m-0 fw-bold">10 AR RAHMAN</h5>
            <small class="text-light">SMA AL MUSLIM</small>
        </div>
        <ul class="list-unstyled components">
            <li class="<?= $page == 'dashboard' ? 'active' : '' ?>"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="<?= $page == 'siswa' ? 'active' : '' ?>"><a href="index.php?page=siswa"><i class="fas fa-user-graduate"></i> Data Siswa</a></li>
            <li class="<?= $page == 'perkembangan' ? 'active' : '' ?>"><a href="index.php?page=perkembangan"><i class="fas fa-clipboard-list"></i> Perkembangan Siswa</a></li>
            <li class="<?= $page == 'hafalan_surat' ? 'active' : '' ?>"><a href="index.php?page=hafalan_surat"><i class="fas fa-book-open"></i> Hafalan Surat Pendek</a></li>
            <li class="<?= $page == 'hafalan_doa' ? 'active' : '' ?>"><a href="index.php?page=hafalan_doa"><i class="fas fa-hands-praying"></i> Hafalan Doa Harian</a></li>
            <li class="<?= $page == 'laporan' ? 'active' : '' ?>"><a href="index.php?page=laporan"><i class="fas fa-file-pdf"></i> Laporan & Print PDF</a></li>
            <li><a href="index.php?logout=true" onclick="return confirm('Yakin ingin keluar?')"><i class="fas fa-sign-out-alt text-danger"></i> Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <div class="navbar-custom d-flex justify-content-between align-items-center">
            <div>
                <h4 class="m-0 fw-bold text-dark">SISTEM INFORMASI WALI KELAS</h4>
                <small class="text-muted">Wali Kelas: Ustad Ali Tamam • Kelas 10 Ar Rahman</small>
            </div>
            <div class="fw-bold text-teal" style="color:#0f766e">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
            </div>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success d-print-none"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger d-print-none"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <?php 
        if ($page == 'dashboard') {
            $jumlah_siswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
            $jumlah_perkembangan = $pdo->query("SELECT COUNT(*) FROM perkembangan_siswa")->fetchColumn();
            echo '<div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card card-stat bg-primary text-white p-4">
                            <h6 class="text-uppercase small fw-bold">Jumlah Siswa</h6>
                            <h2 class="fw-bold m-0">' . $jumlah_siswa . '</h2>
                            <small class="mt-2 d-block">Kelas 10 Ar Rahman</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-stat bg-success text-white p-4">
                            <h6 class="text-uppercase small fw-bold">Catatan Perkembangan</h6>
                            <h2 class="fw-bold m-0">' . $jumlah_perkembangan . '</h2>
                            <small class="mt-2 d-block">Total Catatan Tercatat</small>
                        </div>
                    </div>
                  </div>
                  <div class="card border-0 shadow-sm p-4">
                      <h5 class="fw-bold">Selamat Datang di Sistem Informasi Wali Kelas</h5>
                      <p class="text-muted">Gunakan menu di sebelah kiri untuk mengelola data siswa, catatan perkembangan, penilaian hafalan, serta pencetakan laporan PDF.</p>
                  </div>';
        } elseif ($page == 'siswa') {
            $siswa_list = $pdo->query("SELECT * FROM siswa ORDER BY nama_siswa ASC")->fetchAll();
            echo '<div class="card border-0 shadow-sm p-4">
                    <h4 class="fw-bold mb-3"><i class="fas fa-user-graduate text-teal me-2" style="color:#0f766e"></i>Daftar Siswa Kelas 10 Ar Rahman</h4>
                    <table class="table table-hover align-middle">
                        <thead class="table-light"><tr><th>No</th><th>No Induk</th><th>Nama Siswa</th><th>Status</th></tr></thead>
                        <tbody>';
            foreach ($siswa_list as $i => $s) {
                echo '<tr><td>'.($i+1).'</td><td><code>'.htmlspecialchars($s['nomor_induk']).'</code></td><td class="fw-bold">'.htmlspecialchars($s['nama_siswa']).'</td><td><span class="badge bg-success">'.htmlspecialchars($s['status']).'</span></td></tr>';
            }
            echo '</tbody></table></div>';
        } elseif ($page == 'perkembangan') {
            // Cek apakah mode edit
            $edit_data = null;
            if (isset($_GET['edit'])) {
                $stmt_edit = $pdo->prepare("SELECT * FROM perkembangan_siswa WHERE id = ?");
                $stmt_edit->execute([$_GET['edit']]);
                $edit_data = $stmt_edit->fetch();
            }

            $siswa_list = $pdo->query("SELECT id, nama_siswa FROM siswa ORDER BY nama_siswa ASC")->fetchAll();
            $perks = $pdo->query("SELECT p.*, s.nama_siswa FROM perkembangan_siswa p JOIN siswa s ON p.siswa_id = s.id ORDER BY p.tanggal DESC")->fetchAll();
            
            echo '
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h4 class="fw-bold mb-3"><i class="fas fa-clipboard-list text-teal me-2" style="color:#0f766e"></i>'.($edit_data ? 'Edit Catatan Perkembangan' : 'Form Input Perkembangan Siswa').'</h4>
                <form method="POST">
                    <input type="hidden" name="id" value="'.($edit_data ? $edit_data['id'] : '').'">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hari / Tanggal</label>
                            <input type="date" name="tanggal" value="'.($edit_data ? $edit_data['tanggal'] : date('Y-m-d')).'" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nama Siswa</label>
                            <select name="siswa_id" class="form-select" required>
                                <option value="">-- Pilih Siswa --</option>';
            foreach($siswa_list as $sl) {
                $sel = ($edit_data && $edit_data['siswa_id'] == $sl['id']) ? 'selected' : '';
                echo '<option value="'.$sl['id'].'" '.$sel.'>'.htmlspecialchars($sl['nama_siswa']).'</option>';
            }
            echo '          </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Perkembangan Siswa</label>
                        <textarea name="perkembangan" rows="3" class="form-control" placeholder="Catat perkembangan akademik atau karakter siswa..." required>'.($edit_data ? htmlspecialchars($edit_data['perkembangan']) : '').'</textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Kendala Siswa</label>
                            <textarea name="kendala" rows="2" class="form-control" placeholder="Kendala atau hambatan siswa...">'.($edit_data ? htmlspecialchars($edit_data['kendala']) : '').'</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tindak Lanjut</label>
                            <textarea name="tindak_lanjut" rows="2" class="form-control" placeholder="Rencana atau aksi tindak lanjut...">'.($edit_data ? htmlspecialchars($edit_data['tindak_lanjut']) : '').'</textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Catatan Wali Kelas</label>
                        <textarea name="catatan_wali" rows="2" class="form-control" placeholder="Catatan khusus wali kelas...">'.($edit_data ? htmlspecialchars($edit_data['catatan_wali']) : '').'</textarea>
                    </div>
                    <button type="submit" name="simpan_perkembangan" class="btn btn-primary fw-bold" style="background:#0f766e; border:none;"><i class="fas fa-save me-1"></i> '.($edit_data ? 'Perbarui Catatan' : 'Simpan Catatan Perkembangan').'</button>
                    '.($edit_data ? '<a href="index.php?page=perkembangan" class="btn btn-secondary ms-2">Batal Edit</a>' : '').'
                </form>
            </div>

            <div class="card border-0 shadow-sm p-4">
                <h4 class="fw-bold mb-3"><i class="fas fa-history text-teal me-2" style="color:#0f766e"></i>Riwayat Perkembangan Siswa</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Perkembangan</th>
                                <th>Kendala</th>
                                <th>Tindak Lanjut</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>';
            if(count($perks) > 0) {
                foreach ($perks as $p) {
                    echo '<tr>
                            <td class="text-nowrap">'.date('d/m/Y', strtotime($p['tanggal'])).'</td>
                            <td class="fw-bold">'.htmlspecialchars($p['nama_siswa']).'</td>
                            <td>'.htmlspecialchars($p['perkembangan']).'</td>
                            <td>'.htmlspecialchars($p['kendala']).'</td>
                            <td>'.htmlspecialchars($p['tindak_lanjut']).'</td>
                            <td>
                                <a href="index.php?page=perkembangan&edit='.$p['id'].'" class="btn btn-sm btn-warning text-white me-1" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="index.php?page=perkembangan&hapus_perkembangan='.$p['id'].'" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm(\'Yakin ingin menghapus catatan ini?\')"><i class="fas fa-trash"></i></a>
                            </td>
                          </tr>';
                }
            } else {
                echo '<tr><td colspan="6" class="text-center text-muted">Belum ada catatan perkembangan siswa.</td></tr>';
            }
            echo '</tbody></table></div></div>';
        } elseif ($page == 'hafalan_surat') {
            $semester = isset($_GET['semester']) ? $_GET['semester'] : 'Gasal';
            $siswa_list = $pdo->query("SELECT * FROM siswa ORDER BY nama_siswa ASC")->fetchAll();
            $stmt_t = $pdo->prepare("SELECT * FROM target_surat WHERE semester = ? ORDER BY urutan ASC");
            $stmt_t->execute([$semester]);
            $targets = $stmt_t->fetchAll();
            
            echo '
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold m-0"><i class="fas fa-book-open text-teal me-2" style="color:#0f766e"></i>Hafalan Surat Pendek (Semester '.$semester.')</h4>
                <div class="btn-group">
                    <a href="index.php?page=hafalan_surat&semester=Gasal" class="btn btn-sm btn-'.($semester=='Gasal'?'primary':'outline-primary').'">Gasal</a>
                    <a href="index.php?page=hafalan_surat&semester=Genap" class="btn btn-sm btn-'.($semester=='Genap'?'primary':'outline-primary').'">Genap</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 mb-4 bg-light">
                <form method="POST" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Pilih Siswa</label>
                        <select name="siswa_id" class="form-select form-select-sm" required>
                            <option value="">-- Siswa --</option>';
            foreach($siswa_list as $s) {
                echo '<option value="'.$s['id'].'">'.htmlspecialchars($s['nama_siswa']).'</option>';
            }
            echo '      </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Target Surat</label>
                        <select name="target_surat_id" class="form-select form-select-sm" required>
                            <option value="">-- Surat --</option>';
            foreach($targets as $t) {
                echo '<option value="'.$t['id'].'">'.htmlspecialchars($t['nama_surat']).'</option>';
            }
            echo '      </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="Belum">Belum</option>
                            <option value="Proses">Proses</option>
                            <option value="Hafal">Hafal</option>
                            <option value="Tuntas">Tuntas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Nilai (0-100)</label>
                        <input type="number" name="nilai" min="0" max="100" class="form-control form-control-sm" value="85" required>
                    </div>
                    <div class="col-md-2">
                        <input type="hidden" name="tanggal" value="'.date('Y-m-d').'">
                        <button type="submit" name="simpan_surat" class="btn btn-primary btn-sm w-100 fw-bold" style="background:#0f766e; border:none;">Simpan Nilai</button>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Siswa</th>';
            foreach($targets as $t) {
                echo '<th class="text-center">'.htmlspecialchars($t['nama_surat']).'</th>';
            }
            echo '          </tr>
                        </thead>
                        <tbody>';
            foreach($siswa_list as $s) {
                echo '<tr><td class="fw-bold text-nowrap">'.htmlspecialchars($s['nama_siswa']).'</td>';
                foreach($targets as $t) {
                    $p_q = $pdo->prepare("SELECT * FROM penilaian_surat WHERE siswa_id = ? AND target_surat_id = ?");
                    $p_q->execute([$s['id'], $t['id']]);
                    $penilaian = $p_q->fetch();
                    $st = $penilaian ? $penilaian['status'] : 'Belum';
                    $bg = 'secondary';
                    if($st=='Proses') $bg='warning text-dark';
                    if($st=='Hafal') $bg='primary';
                    if($st=='Tuntas') $bg='success';
                    echo '<td class="text-center"><span class="badge bg-'.$bg.' w-100 py-1">'.$st.'<br><b>'.($penilaian?$penilaian['nilai']:0).'</b></span></td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table></div></div>';
        } elseif ($page == 'hafalan_doa') {
            $semester = isset($_GET['semester']) ? $_GET['semester'] : 'Gasal';
            $siswa_list = $pdo->query("SELECT * FROM siswa ORDER BY nama_siswa ASC")->fetchAll();
            $stmt_t = $pdo->prepare("SELECT * FROM target_doa WHERE semester = ? ORDER BY urutan ASC");
            $stmt_t->execute([$semester]);
            $targets = $stmt_t->fetchAll();
            
            echo '
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold m-0"><i class="fas fa-hands-praying text-teal me-2" style="color:#0f766e"></i>Hafalan Doa Harian (Semester '.$semester.')</h4>
                <div class="btn-group">
                    <a href="index.php?page=hafalan_doa&semester=Gasal" class="btn btn-sm btn-'.($semester=='Gasal'?'primary':'outline-primary').'">Gasal</a>
                    <a href="index.php?page=hafalan_doa&semester=Genap" class="btn btn-sm btn-'.($semester=='Genap'?'primary':'outline-primary').'">Genap</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm p-4 mb-4 bg-light">
                <form method="POST" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Pilih Siswa</label>
                        <select name="siswa_id" class="form-select form-select-sm" required>
                            <option value="">-- Siswa --</option>';
            foreach($siswa_list as $s) {
                echo '<option value="'.$s['id'].'">'.htmlspecialchars($s['nama_siswa']).'</option>';
            }
            echo '      </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Target Doa</label>
                        <select name="target_doa_id" class="form-select form-select-sm" required>
                            <option value="">-- Doa --</option>';
            foreach($targets as $t) {
                echo '<option value="'.$t['id'].'">'.htmlspecialchars($t['nama_doa']).'</option>';
            }
            echo '      </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="Belum">Belum</option>
                            <option value="Proses">Proses</option>
                            <option value="Hafal">Hafal</option>
                            <option value="Tuntas">Tuntas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Nilai (0-100)</label>
                        <input type="number" name="nilai" min="0" max="100" class="form-control form-control-sm" value="85" required>
                    </div>
                    <div class="col-md-2">
                        <input type="hidden" name="tanggal" value="'.date('Y-m-d').'">
                        <button type="submit" name="simpan_doa" class="btn btn-primary btn-sm w-100 fw-bold" style="background:#0f766e; border:none;">Simpan Nilai</button>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Siswa</th>';
            foreach($targets as $t) {
                echo '<th class="text-center">'.htmlspecialchars($t['nama_doa']).'</th>';
            }
            echo '          </tr>
                        </thead>
                        <tbody>';
            foreach($siswa_list as $s) {
                echo '<tr><td class="fw-bold text-nowrap">'.htmlspecialchars($s['nama_siswa']).'</td>';
                foreach($targets as $t) {
                    $p_q = $pdo->prepare("SELECT * FROM penilaian_doa WHERE siswa_id = ? AND target_doa_id = ?");
                    $p_q->execute([$s['id'], $t['id']]);
                    $penilaian = $p_q->fetch();
                    $st = $penilaian ? $penilaian['status'] : 'Belum';
                    $bg = 'secondary';
                    if($st=='Proses') $bg='warning text-dark';
                    if($st=='Hafal') $bg='primary';
                    if($st=='Tuntas') $bg='success';
                    echo '<td class="text-center"><span class="badge bg-'.$bg.' w-100 py-1">'.$st.'<br><b>'.($penilaian?$penilaian['nilai']:0).'</b></span></td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table></div></div>';
        } elseif ($page == 'laporan') {
            $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'siswa';
            $siswa_list = $pdo->query("SELECT * FROM siswa ORDER BY nama_siswa ASC")->fetchAll();
            
            echo '
            <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
                <h4 class="fw-bold m-0"><i class="fas fa-file-pdf text-teal me-2" style="color:#0f766e"></i>Laporan & Cetak PDF</h4>
                <button onclick="window.print()" class="btn btn-danger fw-bold"><i class="fas fa-print me-1"></i> Print / Simpan PDF</button>
            </div>

            <div class="card border-0 shadow-sm p-3 mb-4 d-print-none">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="page" value="laporan">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Jenis Laporan</label>
                        <select name="jenis" class="form-select">
                            <option value="siswa" '.($jenis=='siswa'?'selected':'').'>Rekap Seluruh Siswa</option>
                            <option value="perkembangan" '.($jenis=='perkembangan'?'selected':'').'>Rekap Perkembangan Siswa</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100 fw-bold">Tampilkan</button>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm p-5 bg-white">
                <div class="text-center mb-4">
                    <h4 class="fw-bold m-0">SMA AL MUSLIM</h4>
                    <h5 class="fw-bold">LAPORAN WALI KELAS 10 AR RAHMAN</h5>
                    <p class="text-muted small m-0">Wali Kelas: Ustad Ali Tamam • Tahun Ajaran 2026/2027</p>
                    <hr>
                </div>';

            if ($jenis == 'siswa') {
                echo '<h5 class="fw-bold mb-3">Daftar Seluruh Siswa Kelas 10 Ar Rahman</h5>
                      <table class="table table-bordered align-middle">
                          <thead class="table-light"><tr><th>No</th><th>Nomor Induk</th><th>Nama Siswa</th><th>Kelas</th><th>Status</th></tr></thead>
                          <tbody>';
                foreach ($siswa_list as $i => $s) {
                    echo '<tr><td>'.($i+1).'</td><td>'.htmlspecialchars($s['nomor_induk']).'</td><td class="fw-bold">'.htmlspecialchars($s['nama_siswa']).'</td><td>'.htmlspecialchars($s['kelas']).'</td><td>'.htmlspecialchars($s['status']).'</td></tr>';
                }
                echo '</tbody></table>';
            } else {
                $perks = $pdo->query("SELECT p.*, s.nama_siswa FROM perkembangan_siswa p JOIN siswa s ON p.siswa_id = s.id ORDER BY p.tanggal DESC")->fetchAll();
                echo '<h5 class="fw-bold mb-3">Rekap Perkembangan Siswa</h5>
                      <table class="table table-bordered align-middle">
                          <thead class="table-light"><tr><th>Tanggal</th><th>Nama Siswa</th><th>Perkembangan</th><th>Kendala</th><th>Tindak Lanjut</th></tr></thead>
                          <tbody>';
                if(count($perks) > 0) {
                    foreach ($perks as $p) {
                        echo '<tr><td>'.date('d/m/Y', strtotime($p['tanggal'])).'</td><td class="fw-bold">'.htmlspecialchars($p['nama_siswa']).'</td><td>'.htmlspecialchars($p['perkembangan']).'</td><td>'.htmlspecialchars($p['kendala']).'</td><td>'.htmlspecialchars($p['tindak_lanjut']).'</td></tr>';
                    }
                } else {
                    echo '<tr><td colspan="5" class="text-center text-muted">Belum ada catatan perkembangan.</td></tr>';
                }
                echo '</tbody></table>';
            }

            echo '  <div class="row mt-5">
                        <div class="col-md-8"></div>
                        <div class="col-md-4 text-center">
                            <p class="mb-5">Sidoarjo, '.date('d F Y').'<br>Wali Kelas 10 Ar Rahman</p>
                            <br><br>
                            <p class="fw-bold m-0">Ustad Ali Tamam</p>
                        </div>
                    </div>
                </div>';
        }
        ?>

        <footer class="text-center mt-5 py-3 text-muted small d-print-none">
            &copy; <?= date('Y') ?> Sistem Informasi Wali Kelas 10 Ar Rahman • SMA Al Muslim
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>