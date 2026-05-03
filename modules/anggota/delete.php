<?php
require_once '../../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT foto FROM anggota WHERE id_anggota = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($data) {
    if (!empty($data['foto']) && file_exists('uploads/' . $data['foto'])) {
        unlink('uploads/' . $data['foto']);
    }

    $hapus = $conn->prepare("DELETE FROM anggota WHERE id_anggota = ?");
    $hapus->bind_param("i", $id);
    $hapus->execute();
}

header("Location: index.php?pesan=hapus");
exit;