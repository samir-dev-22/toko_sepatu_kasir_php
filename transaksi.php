<?php
require 'auth.php';

// ===============================
// DATA PRODUK
// ===============================
$products = [
    'PRD001' => [
        'id_produk' => 'PRD001',
        'nama_produk' => 'kid pink shoes',
        'deskripsi' => 'Sneakers kasual dengan desain modern, nyaman digunakan untuk aktivitas sehari-hari.',
        'ukuran' => [38, 39, 40, 41, 42],
        'harga' => 300000,
        'stok' => 100,
        'gambar' => 'gambar_product/sepatu1.png'
    ],

    'PRD002' => [
        'id_produk' => 'PRD002',
        'nama_produk' => 'Sepatu B',
        'deskripsi' => 'Sneakers kasual dengan desain modern, nyaman digunakan untuk aktivitas sehari-hari.',
        'ukuran' => [39, 40, 41, 42, 43],
        'harga' => 450000,
        'stok' => 100,
        'gambar' => 'gambar_product/sepatu2.png'
    ],

    'PRD003' => [
        'id_produk' => 'PRD003',
        'nama_produk' => 'Sepatu C',
        'deskripsi' => 'Sneakers kasual dengan desain modern, nyaman digunakan untuk aktivitas sehari-hari.',
        'ukuran' => [38, 39, 40, 41, 42],
        'harga' => 250000,
        'stok' => 100,
        'gambar' => 'gambar_product/sepatu3.png'
    ],

    'PRD004' => [
        'id_produk' => 'PRD004',
        'nama_produk' => 'Sepatu D',
        'deskripsi' => 'Sneakers kasual dengan desain modern, nyaman digunakan untuk aktivitas sehari-hari.',
        'ukuran' => [40, 41, 42, 43, 44],
        'harga' => 250000,
        'stok' => 100,
        'gambar' => 'gambar_product/sepatu4.png'
    ]
];

// ===============================
// INISIALISASI SESSION
// ===============================
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['members'])) {
    $_SESSION['members'] = [];
}

if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = [];
}

$error = '';

// ===============================
// TAMBAH PRODUK KE KERANJANG
// ===============================
if (isset($_POST['add_product'])) {
    $id = $_POST['id_produk'] ?? '';
    $ukuran = (int) ($_POST['ukuran'] ?? 0);

    $qty = filter_var(
        $_POST['jumlah'] ?? 1,
        FILTER_VALIDATE_INT
    );

    if (!isset($products[$id])) {
        $error = 'Produk tidak ditemukan.';
    } elseif ($qty === false || $qty < 1) {
        $error = 'Jumlah produk minimal 1.';
    } elseif (!in_array($ukuran, $products[$id]['ukuran'], true)) {
        $error = 'Ukuran sepatu tidak tersedia.';
    } else {
        // Kunci keranjang berbeda untuk setiap ukuran produk.
        $cartKey = $id . '-' . $ukuran;

        $already = $_SESSION['cart'][$cartKey]['jumlah'] ?? 0;

        if ($already + $qty > $products[$id]['stok']) {
            $error = 'Jumlah melebihi stok produk yang tersedia.';
        } else {
            $product = $products[$id];

            // Simpan ukuran yang dipilih untuk item keranjang ini.
            $product['ukuran'] = $ukuran;

            $_SESSION['cart'][$cartKey] = [
                'product' => $product,
                'jumlah' => $already + $qty
            ];
        }
    }
}

// ===============================
// HAPUS PRODUK DARI KERANJANG
// ===============================
if (isset($_POST['remove_product'])) {
    $id = $_POST['remove_product'];

    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
}

// ===============================
// KOSONGKAN KERANJANG
// ===============================
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
}

// ===============================
// PROSES PEMBAYARAN
// ===============================
if (isset($_POST['process_payment'])) {
    $cart = $_SESSION['cart'];

    $customerId = trim($_POST['id_pelanggan'] ?? '');
    $method = $_POST['metode_pembayaran'] ?? 'Tunai';

    $paid = filter_var(
        $_POST['jumlah_bayar'] ?? 0,
        FILTER_VALIDATE_INT
    );

    $membersById = [];

    foreach ($_SESSION['members'] as $member) {
        if (isset($member['id_pelanggan'])) {
            $membersById[$member['id_pelanggan']] = $member;
        }
    }

    $total = 0;

    foreach ($cart as $row) {
        $total += $row['product']['harga'] * $row['jumlah'];
    }

    $metodeValid = [
        'Tunai',
        'QRIS',
        'Transfer',
        'Kartu Debit'
    ];

    if (empty($cart)) {
        $error = 'Keranjang masih kosong.';
    } elseif (
        $customerId !== '' &&
        !isset($membersById[$customerId])
    ) {
        $error = 'Member tidak ditemukan. Pilih member terdaftar atau Nonmember.';
    } elseif (!in_array($method, $metodeValid, true)) {
        $error = 'Metode pembayaran tidak valid.';
    } elseif (
        $method === 'Tunai' &&
        ($paid === false || $paid < $total)
    ) {
        $error = 'Jumlah uang diterima belum mencukupi.';
    } else {
        $pegawai = $_SESSION['pegawai'] ?? [];

        $trxId = 'TRX' . date('YmdHis');

        $trx = [
            'id_transaksi' => $trxId,
            'id_pegawai' => $pegawai['id_pegawai'] ?? '-',
            'nama_pegawai' => $pegawai['nama_pegawai'] ?? 'Kasir',

            'id_pelanggan' => $customerId !== ''
                ? $customerId
                : null,

            'nama_pelanggan' => $customerId !== ''
                ? $membersById[$customerId]['nama_pelanggan']
                : 'Nonmember',

            'tgl_transaksi' => date('d-m-Y H:i:s'),
            'metode_pembayaran' => $method,
            'total' => $total,

            'jumlah_bayar' => $method === 'Tunai'
                ? (int) $paid
                : $total,

            'kembalian' => $method === 'Tunai'
                ? (int) $paid - $total
                : 0,

            'items' => []
        ];

        foreach ($cart as $row) {
            $item = $row['product'];

            $trx['items'][] = [
                'id_produk' => $item['id_produk'],
                'nama_produk' => $item['nama_produk'],
                'ukuran' => $item['ukuran'],
                'harga' => $item['harga'],
                'jumlah' => $row['jumlah'],
                'subtotal' => $item['harga'] * $row['jumlah']
            ];
        }

        // Simpan transaksi untuk halaman riwayat.
        $_SESSION['transactions'][] = $trx;

        // Simpan transaksi terakhir untuk halaman struk.
        $_SESSION['last_receipt'] = $trx;

        // Kosongkan keranjang setelah pembayaran berhasil.
        $_SESSION['cart'] = [];

        header('Location: struk.php');
        exit;
    }
}

// ===============================
// HITUNG TOTAL KERANJANG
// ===============================
$cart = $_SESSION['cart'];
$total = 0;

foreach ($cart as $row) {
    $total += $row['product']['harga'] * $row['jumlah'];
}

$members = $_SESSION['members'];
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Transaksi Penjualan</title>

<link rel="stylesheet" href="style.css">

<style>
.product-placeholder {
    width: 85px;
    height: 85px;
    flex-shrink: 0;
    overflow: hidden;
    border-radius: 10px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.product-row {
    display: flex;
    align-items: center;
    gap: 14px;
}

.product-info {
    flex: 1;
    min-width: 0;
}

.add-form select {
    max-width: 105px;
    min-width: 92px;
    padding: 8px 6px;
    border: 1px solid #d5dfed;
    border-radius: 8px;
    background: #fff;
    color: #142b49;
}

.product-info strong,
.product-info small,
.product-info b {
    display: block;
    margin-bottom: 5px;
}

.product-image-error {
    font-size: 28px;
}

@media (max-width: 600px) {
    .product-row {
        flex-wrap: wrap;
    }
}
</style>
</head>

<body>
<div class="layout">

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="brand">
        <span class="brand-mark small">TS</span>
        <span>HOTIC Shoe Store</span>
    </div>

    <nav>
        <a href="dashboard.php">▦ Dashboard</a>
        <a class="active" href="transaksi.php">🛒 Transaksi</a>
        <a href="member.php">♙ Data Member</a>
        <a href="riwayat.php">◷ Riwayat Transaksi</a>
        <a href="logout.php">↪ Logout</a>
    </nav>

    <div class="sidebar-user">
        <div class="avatar">
            <?= e(substr($_SESSION['pegawai']['nama_pegawai'] ?? 'K', 0, 1)) ?>
        </div>

        <div>
            <strong>
                <?= e($_SESSION['pegawai']['nama_pegawai'] ?? 'Kasir') ?>
            </strong>

            <small>
                <?= e($_SESSION['pegawai']['id_pegawai'] ?? '-') ?>
            </small>
        </div>
    </div>

</aside>

<!-- KONTEN UTAMA -->
<main class="main">

    <header class="topbar">
        <div>
            <h1>Transaksi Penjualan</h1>

            <p class="muted">
                Kasir:
                <?= e($_SESSION['pegawai']['nama_pegawai'] ?? 'Kasir') ?>
                · ID
                <?= e($_SESSION['pegawai']['id_pegawai'] ?? '-') ?>
            </p>
        </div>

        <a class="btn secondary" href="dashboard.php">
            ← Dashboard
        </a>
    </header>

    <?php if ($error): ?>
        <div class="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="two-column transaction-layout">

        <!-- DAFTAR PRODUK -->
        <section class="panel">

            <h2>Daftar Produk</h2>

            <p class="muted">
                Pilih produk, ukuran, dan jumlahnya.
            </p>

            <?php foreach ($products as $p): ?>

                <div class="product-row">

                    <!-- GAMBAR PRODUK -->
                    <div class="product-placeholder">

                        <?php if (!empty($p['gambar'])): ?>

                            <img
                                src="<?= e($p['gambar']) ?>"
                                alt="<?= e($p['nama_produk']) ?>"
                                class="product-image"
                                onerror="this.style.display='none';this.parentElement.textContent='👟';"
                            >

                        <?php else: ?>

                            👟

                        <?php endif; ?>

                    </div>

                    <!-- INFORMASI PRODUK -->
                    <div class="product-info">

                        <strong>
                            <?= e($p['nama_produk']) ?>
                        </strong>

                        <small>
                            Ukuran tersedia:
                            <?= e(implode(', ', $p['ukuran'])) ?>

                            · Stok <?= e($p['stok']) ?>

                            <br>

                            <?= e($p['deskripsi']) ?>
                        </small>

                        <b>
                            <?= rupiah($p['harga']) ?>
                        </b>

                    </div>

                    <!-- FORM TAMBAH PRODUK -->
                    <form method="post" class="add-form">

                        <input
                            type="hidden"
                            name="id_produk"
                            value="<?= e($p['id_produk']) ?>"
                        >

                        <!-- PILIH UKURAN -->
                        <select
                            name="ukuran"
                            aria-label="Pilih ukuran sepatu"
                            required
                        >

                            <?php foreach ($p['ukuran'] as $ukuran): ?>

                                <option value="<?= e($ukuran) ?>">
                                    <?= e($ukuran) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <!-- JUMLAH PRODUK -->
                        <input
                            aria-label="Jumlah"
                            type="number"
                            name="jumlah"
                            min="1"
                            max="<?= e($p['stok']) ?>"
                            value="1"
                            required
                        >

                        <button
                            class="btn small-btn"
                            name="add_product"
                            value="1"
                            type="submit"
                        >
                            Tambah
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        </section>

        <!-- KERANJANG DAN PEMBAYARAN -->
        <section class="panel">

            <div class="panel-heading">

                <h2>Keranjang</h2>

                <form method="post">
                    <button
                        class="text-button"
                        name="clear_cart"
                        value="1"
                    >
                        Kosongkan
                    </button>
                </form>

            </div>

            <?php if (!$cart): ?>

                <div class="empty-state">

                    <div class="empty-icon">🛒</div>

                    <strong>Keranjang kosong</strong>

                    <p class="muted">
                        Tambahkan produk untuk memulai transaksi.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-wrap">

                    <table>

                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($cart as $id => $row): ?>

                                <tr>

                                    <td>
                                        <?= e($row['product']['nama_produk']) ?>

                                        <small class="table-sub">
                                            Ukuran
                                            <?= e($row['product']['ukuran']) ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= e($row['jumlah']) ?>
                                    </td>

                                    <td>
                                        <?= rupiah(
                                            $row['product']['harga']
                                            * $row['jumlah']
                                        ) ?>
                                    </td>

                                    <td>

                                        <form method="post">

                                            <button
                                                class="remove-btn"
                                                name="remove_product"
                                                value="<?= e($id) ?>"
                                            >
                                                ×
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

            <!-- TOTAL -->
            <div class="total-line">

                <span>Total</span>

                <strong>
                    <?= rupiah($total) ?>
                </strong>

            </div>

            <!-- FORM PEMBAYARAN -->
            <form method="post" class="payment-form">

                <label>Pelanggan</label>

                <select name="id_pelanggan">

                    <option value="">
                        Nonmember (tanpa data pelanggan)
                    </option>

                    <?php foreach ($members as $m): ?>

                        <option value="<?= e($m['id_pelanggan']) ?>">
                            <?= e(
                                $m['id_pelanggan'] . ' - '
                                . $m['nama_pelanggan']
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <div class="helper">
                    Jika customer ingin menjadi member, daftarkan melalui menu
                    <a href="member.php">Data Member</a>.
                    Jika tidak, pilih Nonmember.
                </div>

                <label>Metode Pembayaran</label>

                <select
                    name="metode_pembayaran"
                    id="metode"
                    onchange="document.getElementById('cash-field').style.display=this.value==='Tunai'?'block':'none'"
                >

                    <option>Tunai</option>
                    <option>QRIS</option>
                    <option>Transfer</option>
                    <option>Kartu Debit</option>

                </select>

                <div id="cash-field">

                    <label>Jumlah Uang Diterima (Rp)</label>

                    <input
                        type="number"
                        name="jumlah_bayar"
                        min="0"
                        step="1"
                        value="<?= e($total) ?>"
                    >

                </div>

                <button
                    class="btn full"
                    type="submit"
                    name="process_payment"
                    value="1"
                    <?= !$cart ? 'disabled' : '' ?>
                >
                    Proses Pembayaran & Cetak Struk
                </button>

            </form>

        </section>

    </div>

</main>
</div>

</body>
</html>