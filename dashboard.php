<?php
require 'auth.php';
$members = $_SESSION['members'] ?? [];
$transactions = $_SESSION['transactions'] ?? [];
$totalHariIni = 0;
foreach ($transactions as $trx) $totalHariIni += $trx['total'];
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Dashboard Kasir</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand"><span class="brand-mark small">TS</span><span>HOTIC Shoe Store</span></div>
        <nav>
            <a class="active" href="dashboard.php">▦ Dashboard</a>
            <a href="transaksi.php">🛒 Transaksi</a>
            <a href="member.php">♙ Data Member</a>
            <a href="riwayat.php">◷ Riwayat Transaksi</a>
            <a href="logout.php">↪ Logout</a>
        </nav>
        <div class="sidebar-user"><div class="avatar"><?= e(substr($_SESSION['pegawai']['nama_pegawai'],0,1)) ?></div><div><strong><?= e($_SESSION['pegawai']['nama_pegawai']) ?></strong><small><?= e($_SESSION['pegawai']['id_pegawai']) ?></small></div></div>
    </aside>
    <main class="main">
        <header class="topbar"><div><h1>Dashboard</h1><p class="muted">Selamat datang, <?= e($_SESSION['pegawai']['nama_pegawai']) ?>.</p></div><span class="pill">Kasir aktif</span></header>
        <section class="stats">
            <div class="stat-card"><span>Total Member</span><strong><?= count($members) ?></strong><small>Member terdaftar</small></div>
            <div class="stat-card"><span>Total Transaksi</span><strong><?= count($transactions) ?></strong><small>Transaksi demo tersimpan</small></div>
            <div class="stat-card"><span>Total Penjualan</span><strong><?= rupiah($totalHariIni) ?></strong><small>Dari transaksi demo</small></div>
        </section>
        <section class="panel">
            <h2>Menu Kasir</h2>
            <p class="muted">Pilih menu yang ingin digunakan.</p>
            <div class="menu-cards">
                <a class="menu-card" href="transaksi.php"><div class="menu-icon blue">🛒</div><h3>Transaksi Penjualan</h3><span class="link-text">Buka transaksi →</span></a>
                <a class="menu-card" href="member.php"><div class="menu-icon green">♙</div><h3>Data Member</h3><span class="link-text">Kelola member →</span></a>
                <a class="menu-card" href="riwayat.php"><div class="menu-icon orange">◷</div><h3>Riwayat Transaksi</h3><span class="link-text">Lihat riwayat →</span></a>
            </div>
        </section>
    </main>
</div>
</body>
</html>
