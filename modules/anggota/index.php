<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$jenis_kelamin = $_GET['jenis_kelamin'] ?? '';

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(nama LIKE ? OR email LIKE ? OR telepon LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if ($status !== '' && in_array($status, ['Aktif', 'Nonaktif'])) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($jenis_kelamin !== '' && in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'])) {
    $where[] = "jenis_kelamin = ?";
    $params[] = $jenis_kelamin;
    $types .= 's';
}

$whereSql = '';
if ($where) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT * FROM anggota $whereSql ORDER BY id_anggota DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);

if ($types !== '') {
    $types2 = $types . 'ii';
    $params2 = $params;
    $params2[] = $start;
    $params2[] = $limit;
    $stmt->bind_param($types2, ...$params2);
} else {
    $stmt->bind_param('ii', $start, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$sqlTotal = "SELECT COUNT(*) AS total FROM anggota $whereSql";
$stmtTotal = $conn->prepare($sqlTotal);

if ($types !== '') {
    $stmtTotal->bind_param($types, ...$params);
}

$stmtTotal->execute();
$total = $stmtTotal->get_result()->fetch_assoc()['total'];
$pages = ceil($total / $limit);

$totalAnggota = $conn->query("SELECT COUNT(*) AS total FROM anggota")->fetch_assoc()['total'];
$totalAktif = $conn->query("SELECT COUNT(*) AS total FROM anggota WHERE status='Aktif'")->fetch_assoc()['total'];
$totalNonaktif = $conn->query("SELECT COUNT(*) AS total FROM anggota WHERE status='Nonaktif'")->fetch_assoc()['total'];
?>

<div class="container py-4">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Anggota</h6>
                    <h3 class="mb-0"><?= $totalAnggota ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Aktif</h6>
                    <h3 class="mb-0 text-success"><?= $totalAktif ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Nonaktif</h6>
                    <h3 class="mb-0 text-secondary"><?= $totalNonaktif ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="mb-0">Data Anggota</h4>
                <a href="create.php" class="btn btn-success">+ Tambah Anggota</a>
            </div>
        </div>
        <div class="card-body">
            <form class="row g-2 mb-3" method="GET">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, email, telepon..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Nonaktif" <?= $status === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="jenis_kelamin" class="form-select">
                        <option value="">Semua Jenis Kelamin</option>
                        <option value="Laki-laki" <?= $jenis_kelamin === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= $jenis_kelamin === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:70px;">Foto</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Jenis Kelamin</th>
                            <th>Status</th>
                            <th style="width:160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['foto'])): ?>
                                            <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Foto" class="rounded" style="width:50px;height:50px;object-fit:cover;">
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['kode_anggota']) ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['telepon']) ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($row['jenis_kelamin']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'Aktif'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?= $row['id_anggota'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete.php?id=<?= $row['id_anggota'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Data tidak ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <nav>
                <ul class="pagination justify-content-end mb-0">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&jenis_kelamin=<?= urlencode($jenis_kelamin) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>