
<?php
session_start();

if (isset($_SESSION['pegawai'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Daftar akun pegawai demo
    $users = [
        [
            'username' => 'kasir',
            'password' => '12345',
            'id_pegawai' => 'PG001',
            'nama_pegawai' => 'Kasir Demo'
        ],
        [
            'username' => 'tama',
            'password' => 'tama2026',
            'id_pegawai' => 'PG002',
            'nama_pegawai' => 'Tama Adrian'
        ],
        [
            'username' => 'kasir2',
            'password' => 'kasir2026',
            'id_pegawai' => 'PG003',
            'nama_pegawai' => 'Kasir Kedua'
        ],
        [
            'username' => 'saputra',
            'password' => 'shoes23',
            'id_pegawai' => 'PG004',
            'nama_pegawai' => 'samir'
        ]
    ];

    $loginBerhasil = false;

    foreach ($users as $user) {
        if (
            $username === $user['username'] &&
            $password === $user['password']
        ) {
            session_regenerate_id(true);

            $_SESSION['pegawai'] = [
                'id_pegawai' => $user['id_pegawai'],
                'nama_pegawai' => $user['nama_pegawai'],
                'username' => $user['username']
            ];

            $loginBerhasil = true;
            break;
        }
    }

    if ($loginBerhasil) {
        if (!isset($_SESSION['members'])) {
            $_SESSION['members'] = [];
        }

        if (!isset($_SESSION['transactions'])) {
            $_SESSION['transactions'] = [];
        }

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Username atau password salah.';
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Kasir - Toko Sepatu</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">

    <div class="login-card">

        <div class="brand-mark">TS</div>

        <h1>Toko Sepatu</h1>

        <p class="muted">
            Login untuk melanjutkan ke sistem kasir
        </p>

        <?php if ($error !== ''): ?>
            <div class="alert">
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">

            <label for="username">Username</label>

            <input
                id="username"
                name="username"
                type="text"
                required
                autocomplete="username"
                placeholder="Masukkan username"
                value="<?= htmlspecialchars(
                    $_POST['username'] ?? '',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <label for="password">Password</label>

            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan password"
            >

            <button class="btn full" type="submit">
                Login
            </button>

        </form>

    </div>

</body>
</html>
