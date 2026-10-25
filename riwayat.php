
<?php
require 'auth.php';

if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}

// Hapus transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['hapus_transaksi'])) {

    $idHapus = $_POST['hapus_transaksi'];

    foreach ($_SESSION['transactions'] as $key => $trx) {
        if (($trx['id_transaksi'] ?? '') === $idHapus) {
            unset($_SESSION['transactions'][$key]);
            break;
        }
    }

    $_SESSION['transactions'] = array_values(
        $_SESSION['transactions']
    );

    header('Location: riwayat.php');
    exit;
}

// Download struk dalam format HTML
if (isset($_GET['download'])) {
    $idDownload = $_GET['download'];
    $transaksi = null;

    foreach ($_SESSION['transactions'] as $trx) {
        if (($trx['id_transaksi'] ?? '') === $idDownload) {
            $transaksi = $trx;
            break;
        }
    }

    if (!$transaksi) {
        http_response_code(404);
        exit('Transaksi tidak ditemukan.');
    }

    header('Content-Type: text/html; charset=UTF-8');
    header(
        'Content-Disposition: attachment; filename="struk-' .
        preg_replace('/[^a-zA-Z0-9_-]/', '', $idDownload) .
        '.html"'
    );

    $items = $transaksi['items']
        ?? $transaksi['detail']
        ?? [];

    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Struk Pembelian</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 400px;
                margin: 25px auto;
                padding: 20px;
                color: #222;
            }
            h2, .center { text-align: center; }
            table { width: 100%; border-collapse: collapse; }
            td { padding: 7px 0; vertical-align: top; }
            .right { text-align: right; }
            .total {
                border-top: 1px dashed #555;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <h2>TOKO SEPATU</h2>
        <p class="center">Struk Pembelian</p>
        <hr>

        <p>
            No. Transaksi:
            <?= e($transaksi['id_transaksi'] ?? '-') ?><br>

            Tanggal:
            <?= e($transaksi['tgl_transaksi']
                ?? $transaksi['tanggal'] ?? '-') ?><br>

            ID Pegawai:
            <?= e($transaksi['id_pegawai'] ?? '-') ?><br>

            Pelanggan:
            <?= e($transaksi['nama_pelanggan'] ?? 'Non-member') ?>
        </p>

        <table>
            <?php foreach ($items as $item): ?>
                <?php
                $nama = $item['nama_produk'] ?? $item['nama'] ?? '-';
                $jumlah = (int)($item['jumlah'] ?? 0);
                $harga = (float)($item['harga'] ?? 0);
                $subtotal = (float)($item['subtotal']
                    ?? ($jumlah * $harga));
                ?>
                <tr>
                    <td>
                        <?= e($nama) ?><br>
                        <?= $jumlah ?> x <?= rupiah($harga) ?>
                    </td>
                    <td class="right"><?= rupiah($subtotal) ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="total">
                <td>Total</td>
                <td class="right">
                    <?= rupiah($transaksi['total'] ?? 0) ?>
                </td>
            </tr>
            <tr>
                <td>Metode</td>
                <td class="right">
                    <?= e($transaksi['metode_pembayaran'] ?? '-') ?>
                </td>
            </tr>
            <tr>
                <td>Bayar</td>
                <td class="right">
                    <?= rupiah($transaksi['bayar'] ?? 0) ?>
                </td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="right">
                    <?= rupiah($transaksi['kembalian'] ?? 0) ?>
                </td>
            </tr>
        </table>

        <hr>
        <p class="center">Terima kasih sudah berbelanja!</p>
        <p class="center">Simpan file ini untuk arsip struk.</p>
    </body>
    </html>
    <?php
    exit;
}

// Urutkan transaksi terbaru di atas
$transactions = array_reverse($_SESSION['transactions'] ?? []);
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Transaksi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <span class="brand-mark small">TS</span>
            <span>Toko Sepatu</span>
        </div>

        <nav>
            <a href="dashboard.php">▦ Dashboard</a>
            <a href="transaksi.php">🛒 Transaksi</a>
            <a href="member.php">♙ Data Member</a>
            <a class="active" href="riwayat.php">◷ Riwayat Transaksi</a>
            <a href="logout.php">↪ Logout</a>
        </nav>

        <div class="sidebar-user">
            <div class="avatar">
                <?= e(substr($_SESSION['pegawai']['nama_pegawai'] ?? 'K', 0, 1)) ?>
            </div>
            <div>
                <strong><?= e($_SESSION['pegawai']['nama_pegawai'] ?? 'Kasir') ?></strong>
                <small><?= e($_SESSION['pegawai']['id_pegawai'] ?? '-') ?></small>
            </div>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Riwayat Transaksi</h1>
                <p class="muted">Daftar transaksi yang dibuat selama sesi demo.</p>
            </div>
            <a class="btn" href="transaksi.php">+ Transaksi Baru</a>
        </header>

        <section class="panel">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>ID Pegawai</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if (!$transactions): ?>
                        <tr>
                            <td colspan="7" class="empty-cell">
                                Belum ada transaksi.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $trx): ?>
                            <tr>
                                <td><?= e($trx['id_transaksi'] ?? '-') ?></td>
                                <td><?= e($trx['tgl_transaksi'] ?? $trx['tanggal'] ?? '-') ?></td>
                                <td><?= e($trx['id_pegawai'] ?? '-') ?></td>
                                <td><?= e($trx['nama_pelanggan'] ?? 'Non-member') ?></td>
                                <td><?= rupiah($trx['total'] ?? 0) ?></td>
                                <td><?= e($trx['metode_pembayaran'] ?? '-') ?></td>
                                <td>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;">

                                        <a class="btn"
                                           href="struk.php?id=<?= urlencode($trx['id_transaksi'] ?? '') ?>">
                                            Lihat Struk
                                        </a>

                                        <a class="btn"
                                           href="riwayat.php?download=<?= urlencode($trx['id_transaksi'] ?? '') ?>">
                                            Download
                                        </a>

                                        <form method="post"
                                              onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                            <input type="hidden"
                                                   name="hapus_transaksi"
                                                   value="<?= e($trx['id_transaksi'] ?? '') ?>">

                                            <button class="btn"
                                                    type="submit">
                                                Hapus
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>
