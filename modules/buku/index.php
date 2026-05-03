<?php
$page_title = "Data Buku";
require_once '../../config/database.php';
require_once '../../includes/header.php';
 
// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
 
// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
 
// Build query
if (!empty($search)) {
    // Query dengan search
    $query = "SELECT * FROM buku 
              WHERE judul LIKE ? OR pengarang LIKE ? OR kategori LIKE ?
              ORDER BY created_at DESC 
              LIMIT ? OFFSET ?";
    
    $search_param = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Count total untuk pagination
    $count_query = "SELECT COUNT(*) as total FROM buku 
                    WHERE judul LIKE ? OR pengarang LIKE ? OR kategori LIKE ?";
    $stmt_count = $conn->prepare($count_query);
    $stmt_count->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
    
} else {
    // Query tanpa search
    $query = "SELECT * FROM buku ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Count total
    $total_rows = $conn->query("SELECT COUNT(*) as total FROM buku")->fetch_assoc()['total'];
}
 
// Hitung total halaman
$total_pages = ceil($total_rows / $limit);
?>
 
<div class="container">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="bi bi-book"></i> Data Buku Perpustakaan</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Buku Baru
            </a>
        </div>
    </div>
    
    <?php
    // Success/Error messages (sama seperti sebelumnya)
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show">';
        echo '<i class="bi bi-check-circle"></i> ' . htmlspecialchars($_GET['success']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
    
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show">';
        echo '<i class="bi bi-x-circle"></i> ' . htmlspecialchars($_GET['error']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
    ?>
    
    <!-- Search Box -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="">
                <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Cari judul, pengarang, atau kategori...">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                Daftar Buku
                <?php if (!empty($search)): ?>
                    <span class="badge bg-light text-dark">
                        Hasil pencarian: "<?php echo htmlspecialchars($search); ?>"
                    </span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="100">Kode</th>
                            <th>Judul Buku</th>
                            <th>Kategori</th>
                            <th>Pengarang</th>
                            <th>Penerbit</th>
                            <th width="80">Tahun</th>
                            <th width="120">Harga</th>
                            <th width="60">Stok</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1;
                        while ($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><code><?php echo htmlspecialchars($row['kode_buku']); ?></code></td>
                            <td><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($row['kategori']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['pengarang']); ?></td>
                            <td><?php echo htmlspecialchars($row['penerbit']); ?></td>
                            <td><?php echo $row['tahun_terbit']; ?></td>
                            <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <?php if ($row['stok'] > 0): ?>
                                    <span class="badge bg-success"><?php echo $row['stok']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id_buku']; ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $row['id_buku']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <!-- Previous -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                            Previous
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <!-- Next -->
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-info-circle"></i> 
                <strong>Total:</strong> <?php echo $total_rows; ?> buku terdaftar
                <?php if (!empty($search)): ?>
                    | <strong>Ditemukan:</strong> <?php echo $result->num_rows; ?> buku
                <?php endif; ?>
                | <strong>Halaman:</strong> <?php echo $page; ?> dari <?php echo $total_pages; ?>
            </div>
            
            <?php else: ?>
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle"></i> 
                <?php if (!empty($search)): ?>
                    Tidak ada buku yang cocok dengan pencarian "<?php echo htmlspecialchars($search); ?>"
                <?php else: ?>
                    Belum ada data buku. Silakan tambah buku baru.
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
 
<?php
if (isset($stmt)) $stmt->close();
if (isset($stmt_count)) $stmt_count->close();
closeConnection();
require_once '../../includes/footer.php';
?>