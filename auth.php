<?php
session_start();
if (!isset($_SESSION['pegawai'])) {
    header('Location: index.php');
    exit;
}
function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
