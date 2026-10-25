<?php
require 'auth.php';
if (!isset($_SESSION['members'])) $_SESSION['members'] = [];
$error = '';
$success = '';

// Hapus member berdasarkan ID (versi demo tanpa database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_member'])) {
    $deleteId = (string)($_POST['delete_member'] ?? '');
    $before = count($_SESSION['members']);
    $_SESSION['members'] = array_values(array_filter(
        $_SESSION['members'],
        fn($m) => $m['id_pelanggan'] !== $deleteId
    ));
    $success = count($_SESSION['members']) < $before
        ? "Member $deleteId berhasil dihapus."
        : "Member tidak ditemukan.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_pelanggan'])) {
    $nama = trim($_POST['nama_pelanggan'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    if ($nama === '' || $telepon === '') {
        $error = 'Nama pelanggan dan nomor telepon wajib diisi untuk pendaftaran member.';
    } else {
        $id = 'MBR' . str_pad((string)(count($_SESSION['members']) + 1), 3, '0', STR_PAD_LEFT);
        $_SESSION['members'][] = [
            'id_pelanggan' => $id,
            'nama_pelanggan' => $nama,
            'no_telepon' => $telepon
        ];
        $success = "Member berhasil ditambahkan dengan ID $id.";
    }
}
$members = $_SESSION['members'];
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Data Member</title><link rel="stylesheet" href="style.css"></head>
<body><div class="layout">
<aside class="sidebar"><div class="brand"><span class="brand-mark small">TS</span><span>Toko Sepatu</span></div><nav><a href="dashboard.php">▦ Dashboard</a><a href="transaksi.php">🛒 Transaksi</a><a class="active" href="member.php">♙ Data Member</a><a href="riwayat.php">◷ Riwayat Transaksi</a><a href="logout.php">↪ Logout</a></nav><div class="sidebar-user"><div class="avatar"><?= e(substr($_SESSION['pegawai']['nama_pegawai'],0,1)) ?></div><div><strong><?= e($_SESSION['pegawai']['nama_pegawai']) ?></strong><small><?= e($_SESSION['pegawai']['id_pegawai']) ?></small></div></div></aside>
<main class="main">
<header class="topbar"><div><h1>Data Member</h1><p class="muted">Daftarkan pelanggan yang ingin menjadi member.</p></div><a class="btn secondary" href="dashboard.php">← Dashboard</a></header>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="success"><?= e($success) ?></div><?php endif; ?>
<div class="two-column">
<section class="panel"><h2>Tambah Member</h2><p class="muted">Pendaftaran hanya dilakukan jika pelanggan bersedia menjadi member.</p>
<form method="post">
<label>Nama Pelanggan</label><input name="nama_pelanggan" required placeholder="Contoh: Budi Santoso">
<label>Nomor Telepon</label><input name="no_telepon" required type="tel" placeholder="Contoh: 081234567890">
<button class="btn" type="submit">Simpan Member</button>
</form></section>
<section class="panel"><div class="panel-heading"><h2>Daftar Member</h2><span class="pill"><?= count($members) ?> member</span></div>
<?php if (!$members): ?><div class="empty-state"><div class="empty-icon">♙</div><strong>Belum ada member</strong><p class="muted">Member yang didaftarkan akan muncul di sini.</p></div>
<?php else: ?><div class="table-wrap"><table><thead><tr><th>ID Member</th><th>Nama</th><th>No. Telepon</th><th>Aksi</th></tr></thead><tbody><?php foreach ($members as $m): ?><tr><td><?= e($m['id_pelanggan']) ?></td><td><?= e($m['nama_pelanggan']) ?></td><td><?= e($m['no_telepon']) ?></td><td><form method="post" onsubmit="return confirm('Yakin ingin menghapus member ini?')"><button class="remove-btn" type="submit" name="delete_member" value="<?= e($m['id_pelanggan']) ?>" title="Hapus member">×</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section></div>
<div class="note">Member bersifat opsional saat transaksi. Pelanggan yang tidak ingin menjadi member tetap bisa membeli tanpa mengisi data pelanggan.</div>
</main></div></body></html>
