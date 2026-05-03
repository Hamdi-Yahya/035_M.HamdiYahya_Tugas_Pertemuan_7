<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$error = [];
$old = [
    'kode_anggota' => '',
    'nama' => '',
    'email' => '',
    'telepon' => '',
    'alamat' => '',
    'tanggal_lahir' => '',
    'jenis_kelamin' => 'Laki-laki',
    'pekerjaan' => '',
    'status' => 'Aktif',
    'tanggal_daftar' => date('Y-m-d')
];

if (isset($_POST['submit'])) {
    foreach ($old as $key => $value) {
        if (isset($_POST[$key])) {
            $old[$key] = trim($_POST[$key]);
        }
    }

    if ($old['kode_anggota'] === '') {
        $error['kode_anggota'] = 'Kode anggota wajib diisi';
    } else {
        $cek = $conn->prepare("SELECT id_anggota FROM anggota WHERE kode_anggota = ?");
        $cek->bind_param("s", $old['kode_anggota']);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $error['kode_anggota'] = 'Kode anggota sudah digunakan';
        }
    }

    if ($old['nama'] === '') {
        $error['nama'] = 'Nama wajib diisi';
    }

    if ($old['email'] === '') {
        $error['email'] = 'Email wajib diisi';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error['email'] = 'Format email tidak valid';
    } else {
        $cek = $conn->prepare("SELECT id_anggota FROM anggota WHERE email = ?");
        $cek->bind_param("s", $old['email']);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $error['email'] = 'Email sudah digunakan';
        }
    }

    if ($old['telepon'] === '') {
        $error['telepon'] = 'Telepon wajib diisi';
    } elseif (!preg_match('/^08[0-9]{8,12}$/', $old['telepon'])) {
        $error['telepon'] = 'Format telepon harus 08xxxxxxxxxx';
    }

    if ($old['alamat'] === '') {
        $error['alamat'] = 'Alamat wajib diisi';
    }

    if ($old['tanggal_lahir'] === '') {
        $error['tanggal_lahir'] = 'Tanggal lahir wajib diisi';
    } else {
        $umur = date_diff(date_create($old['tanggal_lahir']), date_create('today'))->y;
        if ($umur < 10) {
            $error['tanggal_lahir'] = 'Umur minimal 10 tahun';
        }
    }

    if (!in_array($old['jenis_kelamin'], ['Laki-laki', 'Perempuan'])) {
        $error['jenis_kelamin'] = 'Jenis kelamin tidak valid';
    }

    if (!in_array($old['status'], ['Aktif', 'Nonaktif'])) {
        $error['status'] = 'Status tidak valid';
    }

    $foto = null;
    if (!empty($_FILES['foto']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error['foto'] = 'Format foto harus jpg, jpeg, png, atau webp';
        } else {
            $foto = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $foto);
        }
    }

    if (empty($error)) {
        $tanggal_daftar = date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO anggota (kode_anggota, nama, email, telepon, alamat, tanggal_lahir, jenis_kelamin, pekerjaan, tanggal_daftar, status, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssssssss",
            $old['kode_anggota'],
            $old['nama'],
            $old['email'],
            $old['telepon'],
            $old['alamat'],
            $old['tanggal_lahir'],
            $old['jenis_kelamin'],
            $old['pekerjaan'],
            $tanggal_daftar,
            $old['status'],
            $foto
        );

        if ($stmt->execute()) {
            header("Location: index.php?pesan=sukses");
            exit;
        } else {
            $error['umum'] = 'Gagal menyimpan data';
        }
    }
}
?>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-0">Tambah Anggota</h4>
        </div>
        <div class="card-body">
            <?php if (isset($error['umum'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error['umum']) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Anggota</label>
                        <input type="text" name="kode_anggota" class="form-control <?= isset($error['kode_anggota']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['kode_anggota']) ?>">
                        <div class="invalid-feedback"><?= $error['kode_anggota'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control <?= isset($error['nama']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['nama']) ?>">
                        <div class="invalid-feedback"><?= $error['nama'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control <?= isset($error['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['email']) ?>">
                        <div class="invalid-feedback"><?= $error['email'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" class="form-control <?= isset($error['telepon']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['telepon']) ?>" placeholder="08xxxxxxxxxx">
                        <div class="invalid-feedback"><?= $error['telepon'] ?? '' ?></div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control <?= isset($error['alamat']) ? 'is-invalid' : '' ?>" rows="3"><?= htmlspecialchars($old['alamat']) ?></textarea>
                        <div class="invalid-feedback"><?= $error['alamat'] ?? '' ?></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control <?= isset($error['tanggal_lahir']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['tanggal_lahir']) ?>">
                        <div class="invalid-feedback"><?= $error['tanggal_lahir'] ?? '' ?></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select <?= isset($error['jenis_kelamin']) ? 'is-invalid' : '' ?>">
                            <option value="Laki-laki" <?= $old['jenis_kelamin'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $old['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <div class="invalid-feedback"><?= $error['jenis_kelamin'] ?? '' ?></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select <?= isset($error['status']) ? 'is-invalid' : '' ?>">
                            <option value="Aktif" <?= $old['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="Nonaktif" <?= $old['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                        <div class="invalid-feedback"><?= $error['status'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="form-control" value="<?= htmlspecialchars($old['pekerjaan']) ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Foto (opsional)</label>
                        <input type="file" name="foto" class="form-control <?= isset($error['foto']) ? 'is-invalid' : '' ?>">
                        <div class="invalid-feedback"><?= $error['foto'] ?? '' ?></div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="submit" class="btn btn-success">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>