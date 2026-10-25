<?php
require 'auth.php';
$trx = $_SESSION['last_receipt'] ?? null;
if (!$trx) { header('Location: transaksi.php'); exit; }
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Struk Penjualan</title><link rel="stylesheet" href="style.css"></head>
<body class="receipt-page"><main class="receipt">
<div class="receipt-actions no-print"><a class="btn secondary" href="transaksi.php">← Transaksi Baru</a><button class="btn" onclick="window.print()">Cetak Struk</button></div>
<div class="receipt-paper">
<div class="receipt-brand"><div class="brand-mark">TS</div><h1>TOKO SEPATU</h1><p>Struk Penjualan</p></div>
<div class="receipt-meta">
<div><span>No. Transaksi</span><strong><?= e($trx['id_transaksi']) ?></strong></div>
<div><span>Tanggal</span><strong><?= e($trx['tgl_transaksi']) ?></strong></div>
<div><span>ID Pegawai</span><strong><?= e($trx['id_pegawai']) ?></strong></div>
<div><span>Nama Kasir</span><strong><?= e($trx['nama_pegawai']) ?></strong></div>
<div><span>Pelanggan</span><strong><?= e($trx['nama_pelanggan']) ?></strong></div>
</div>
<div class="receipt-items"><div class="receipt-table-head"><span>Produk</span><span>Jumlah</span><span>Subtotal</span></div>
<?php foreach ($trx['items'] as $item): ?><div class="receipt-item"><span><?= e($item['nama_produk']) ?><small>Ukuran <?= e($item['ukuran']) ?> · <?= rupiah($item['harga']) ?></small></span><span><?= e($item['jumlah']) ?></span><strong><?= rupiah($item['subtotal']) ?></strong></div><?php endforeach; ?>
</div>
<div class="receipt-total"><div><span>Total</span><strong><?= rupiah($trx['total']) ?></strong></div><div><span>Metode Pembayaran</span><strong><?= e($trx['metode_pembayaran']) ?></strong></div><div><span>Uang Diterima</span><strong><?= rupiah($trx['jumlah_bayar']) ?></strong></div><div><span>Kembalian</span><strong><?= rupiah($trx['kembalian']) ?></strong></div></div>
<p class="thanks">Terima kasih telah berbelanja!</p><p class="receipt-foot">Simpan struk ini sebagai bukti transaksi.</p>
</div></main></body></html>
