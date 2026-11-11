<?php
// Form Registrasi Lomba Coding
// Validasi dilakukan di sisi server agar tetap aman meskipun JavaScript dimatikan.

$kategoriWhitelist = [
    'Web Development',
    'Mobile Development',
    'Data Science',
    'Artificial Intelligence',
    'Cyber Security'
];

$pesertaWhitelist = [
    'Individu',
    'Tim'
];

$old = [
    'nama' => '',
    'email' => '',
    'telepon' => '',
    'umur' => '',
    'github' => '',
    'kategori' => '',
    'peserta' => ''
];

$errors = [];
$success = false;

// Mengambil input hanya jika nilainya berupa string.
function ambilInput(string $key): string
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        return '';
    }

    return trim($_POST[$key]);
}

// Semua output dari pengguna wajib di-escape sebelum ditampilkan ke HTML.
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $value) {
        $old[$key] = ambilInput($key);
    }

    // Validasi nama: wajib, 3 sampai 50 karakter.
    if (function_exists('mb_strlen')) {
        $panjangNama = mb_strlen($old['nama'], 'UTF-8');
    } else {
        // Fallback jika ekstensi mbstring belum aktif di server PHP.
        preg_match_all('/./us', $old['nama'], $karakterNama);
        $panjangNama = count($karakterNama[0]);
    }
    if ($old['nama'] === '') {
        $errors['nama'] = 'Nama wajib diisi.';
    } elseif ($panjangNama < 3 || $panjangNama > 50) {
        $errors['nama'] = 'Nama harus terdiri dari 3–50 karakter.';
    }

    // Validasi email.
    if ($old['email'] === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }

    // Nomor HP: menerima format 08..., 62..., atau +62...
    // Spasi dan tanda hubung akan dihapus sebelum diperiksa.
    $teleponBersih = preg_replace('/[\s-]+/', '', $old['telepon']);
    if ($old['telepon'] === '') {
        $errors['telepon'] = 'Nomor HP wajib diisi.';
    } elseif (!preg_match('/^(?:\+62|62|0)8[1-9][0-9]{7,10}$/', $teleponBersih)) {
        $errors['telepon'] = 'Nomor HP tidak valid. Contoh: 081234567890.';
    }

    // Umur harus berada pada rentang 17–19 tahun.
    if ($old['umur'] === '') {
        $errors['umur'] = 'Umur wajib diisi.';
    } elseif (!ctype_digit($old['umur']) || (int) $old['umur'] < 17 || (int) $old['umur'] > 19) {
        $errors['umur'] = 'Pendaftaran hanya untuk peserta berumur 17–19 tahun.';
    }

    // URL GitHub bersifat opsional, tetapi harus valid jika diisi.
    if ($old['github'] !== '') {
        $urlValid = filter_var($old['github'], FILTER_VALIDATE_URL);
        $bagianUrl = parse_url($old['github']);
        $skemaValid = isset($bagianUrl['scheme']) && in_array(strtolower($bagianUrl['scheme']), ['http', 'https'], true);

        if (!$urlValid || !$skemaValid) {
            $errors['github'] = 'URL GitHub tidak valid. Gunakan alamat lengkap, misalnya https://github.com/username.';
        }
    }

    // Validasi kategori menggunakan whitelist.
    if (!in_array($old['kategori'], $kategoriWhitelist, true)) {
        $errors['kategori'] = 'Silakan pilih kategori lomba yang tersedia.';
    }

    // Validasi jenis peserta menggunakan whitelist.
    if (!in_array($old['peserta'], $pesertaWhitelist, true)) {
        $errors['peserta'] = 'Silakan pilih jenis peserta yang tersedia.';
    }

    // Jika tidak ada error, data lolos validasi.
    // Belum disimpan ke database karena tugas tidak menyertakan detail database.
    if (empty($errors)) {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Lomba Coding</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 32px 16px;
            font-family: Arial, Helvetica, sans-serif;
            color: #f4f6fb;
            background:
                radial-gradient(circle at 85% 10%, rgba(255, 91, 48, .19), transparent 28%),
                radial-gradient(circle at 8% 88%, rgba(35, 115, 190, .20), transparent 32%),
                linear-gradient(135deg, #10151f, #202735 55%, #10151f);
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .form-card {
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 13px;
            background: rgba(13, 18, 27, .94);
            box-shadow: 0 18px 50px rgba(0, 0, 0, .35);
        }

        .topbar {
            padding: 17px 22px;
            text-align: center;
            background: #ff5738;
        }

        .topbar h1 {
            margin: 0;
            color: #fff;
            font-size: 22px;
            letter-spacing: .3px;
        }

        .intro {
            padding: 22px 26px 10px;
            color: #cbd2df;
            line-height: 1.65;
            font-size: 14px;
        }

        .intro strong {
            color: #fff;
        }

        form {
            padding: 10px 26px 28px;
        }

        .section-title {
            margin: 20px 0 16px;
            padding: 10px 13px;
            border-radius: 3px;
            color: #fff;
            background: #ff5738;
            font-size: 15px;
            font-weight: bold;
        }

        .field {
            display: grid;
            grid-template-columns: 190px minmax(0, 1fr);
            gap: 14px;
            align-items: start;
            margin-bottom: 17px;
        }

        .field > label,
        .field-label {
            padding-top: 10px;
            color: #eef1f7;
            font-size: 14px;
            font-weight: bold;
        }

        .control {
            min-width: 0;
        }

        input,
        select {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            border: 1px solid #4b5668;
            border-radius: 5px;
            outline: none;
            color: #f8fafc;
            background: #202a39;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s;
        }

        input::placeholder {
            color: #9ba6b6;
        }

        input:focus,
        select:focus {
            border-color: #ff775e;
            box-shadow: 0 0 0 3px rgba(255, 87, 56, .16);
        }

        select option {
            color: #f8fafc;
            background: #202a39;
        }

        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            min-height: 42px;
            align-items: center;
        }

        .radio-option {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #e8edf5;
            font-size: 14px;
            cursor: pointer;
        }

        .radio-option input {
            width: 16px;
            min-height: 16px;
            height: 16px;
            margin: 0;
            accent-color: #ff5738;
        }

        .hint {
            display: block;
            margin-top: 6px;
            color: #9da9ba;
            font-size: 12px;
            line-height: 1.5;
        }

        .error {
            display: block;
            margin-top: 6px;
            color: #ff8d80;
            font-size: 12px;
            line-height: 1.45;
        }

        .age-warning {
            display: none;
            margin-top: 7px;
            color: #ffc36d;
            font-size: 12px;
            line-height: 1.45;
        }

        .notice {
            margin: 18px 0;
            padding: 13px 15px;
            border-radius: 6px;
            line-height: 1.55;
            font-size: 14px;
        }

        .notice.error-box {
            border: 1px solid rgba(255, 111, 97, .45);
            color: #ffd4ce;
            background: rgba(160, 45, 35, .18);
        }

        .notice.success-box {
            border: 1px solid rgba(68, 197, 135, .45);
            color: #c9f7df;
            background: rgba(31, 125, 82, .18);
        }

        .summary {
            margin-top: 13px;
            padding: 14px;
            border-radius: 6px;
            color: #e7edf7;
            background: rgba(255, 255, 255, .055);
            line-height: 1.8;
            overflow-wrap: anywhere;
        }

        .summary strong {
            color: #fff;
        }

        .actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 25px;
        }

        button {
            min-height: 42px;
            padding: 0 24px;
            border: 0;
            border-radius: 4px;
            color: #fff;
            background: #ff5738;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        button:hover {
            background: #e9472b;
            transform: translateY(-1px);
        }

        .reset-button {
            border: 1px solid #687487;
            color: #dce3ed;
            background: transparent;
        }

        .reset-button:hover {
            background: #303b4b;
        }

        .footer-note {
            margin-top: 17px;
            color: #9da9ba;
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 650px) {
            body {
                padding: 14px 9px;
            }

            .topbar h1 {
                font-size: 19px;
            }

            .intro {
                padding: 18px 17px 8px;
            }

            form {
                padding: 8px 17px 22px;
            }

            .field {
                grid-template-columns: 1fr;
                gap: 7px;
                margin-bottom: 15px;
            }

            .field > label,
            .field-label {
                padding-top: 0;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <main class="form-card">
        <header class="topbar">
            <h1>FORMULIR REGISTRASI LOMBA CODING</h1>
        </header>

        <div class="intro">
            <strong>CodeSprint 2026</strong><br>
            Tunjukkan kemampuanmu dalam menyelesaikan tantangan pemrograman.<br>
            Isi formulir pendaftaran dengan data yang benar. Peserta wajib berusia 17–19 tahun.
        </div>

        <?php if ($success): ?>
            <div class="notice success-box" style="margin: 16px 26px 0;">
                <strong>Validasi berhasil!</strong> Data pendaftaran sudah lolos pemeriksaan.
                <div class="summary">
                    <strong>Nama:</strong> <?= e($old['nama']) ?><br>
                    <strong>Email:</strong> <?= e($old['email']) ?><br>
                    <strong>Nomor HP:</strong> <?= e($old['telepon']) ?><br>
                    <strong>Umur:</strong> <?= e($old['umur']) ?> tahun<br>
                    <strong>GitHub:</strong>
                    <?php if ($old['github'] !== ''): ?>
                        <?= e($old['github']) ?>
                    <?php else: ?>
                        Tidak dicantumkan
                    <?php endif; ?><br>
                    <strong>Kategori:</strong> <?= e($old['kategori']) ?><br>
                    <strong>Jenis peserta:</strong> <?= e($old['peserta']) ?>
                </div>
                <span class="hint">Catatan: contoh ini baru melakukan validasi dan menampilkan ringkasan. Data belum disimpan ke database.</span>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errors)): ?>
            <div class="notice error-box" style="margin: 16px 26px 0;">
                <strong>Formulir belum dapat dikirim.</strong> Periksa kembali kolom yang memiliki pesan kesalahan.
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <h2 class="section-title">Informasi Peserta</h2>

            <div class="field">
                <label for="nama">Nama Lengkap</label>
                <div class="control">
                    <input type="text" id="nama" name="nama" minlength="3" maxlength="50"
                           placeholder="Masukkan nama lengkap" value="<?= e($old['nama']) ?>" required>
                    <?php if (isset($errors['nama'])): ?><span class="error"><?= e($errors['nama']) ?></span><?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label for="email">Alamat Email</label>
                <div class="control">
                    <input type="email" id="email" name="email"
                           placeholder="contoh@email.com" value="<?= e($old['email']) ?>" required>
                    <?php if (isset($errors['email'])): ?><span class="error"><?= e($errors['email']) ?></span><?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label for="telepon">Nomor HP</label>
                <div class="control">
                    <input type="tel" id="telepon" name="telepon" inputmode="tel"
                           placeholder="081234567890" value="<?= e($old['telepon']) ?>" required>
                    <span class="hint">Gunakan nomor Indonesia, contoh: 081234567890 atau +6281234567890.</span>
                    <?php if (isset($errors['telepon'])): ?><span class="error"><?= e($errors['telepon']) ?></span><?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label for="umur">Umur</label>
                <div class="control">
                    <input type="number" id="umur" name="umur" min="1" max="100" step="1"
                           placeholder="Masukkan umur" value="<?= e($old['umur']) ?>" required>
                    <span class="hint">Rentang umur yang diperbolehkan: 17–19 tahun.</span>
                    <span class="age-warning" id="ageWarning" role="status"></span>
                    <?php if (isset($errors['umur'])): ?><span class="error"><?= e($errors['umur']) ?></span><?php endif; ?>
                </div>
            </div>

            <h2 class="section-title">Informasi Lomba</h2>

            <div class="field">
                <label for="github">URL Repositori GitHub</label>
                <div class="control">
                    <input type="url" id="github" name="github"
                           placeholder="https://github.com/username" value="<?= e($old['github']) ?>">
                    <span class="hint">Opsional. Masukkan URL lengkap jika ingin mencantumkan repositori.</span>
                    <?php if (isset($errors['github'])): ?><span class="error"><?= e($errors['github']) ?></span><?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label for="kategori">Kategori Lomba</label>
                <div class="control">
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih kategori --</option>
                        <?php foreach ($kategoriWhitelist as $kategori): ?>
                            <option value="<?= e($kategori) ?>" <?= $old['kategori'] === $kategori ? 'selected' : '' ?>>
                                <?= e($kategori) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['kategori'])): ?><span class="error"><?= e($errors['kategori']) ?></span><?php endif; ?>
                </div>
            </div>

            <div class="field">
                <span class="field-label">Jenis Peserta</span>
                <div class="control">
                    <div class="radio-group">
                        <?php foreach ($pesertaWhitelist as $jenis): ?>
                            <label class="radio-option">
                                <input type="radio" name="peserta" value="<?= e($jenis) ?>"
                                    <?= $old['peserta'] === $jenis ? 'checked' : '' ?> required>
                                <?= e($jenis) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['peserta'])): ?><span class="error"><?= e($errors['peserta']) ?></span><?php endif; ?>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Kirim Pendaftaran</button>
                <button type="reset" class="reset-button">Reset Form</button>
            </div>

            <p class="footer-note">Pastikan seluruh data sudah benar sebelum menekan tombol Kirim Pendaftaran.</p>
        </form>
    </main>
</div>

<script>
    // Memberi peringatan umur secara langsung. Validasi utama tetap dilakukan oleh PHP.
    const inputUmur = document.getElementById('umur');
    const ageWarning = document.getElementById('ageWarning');

    function periksaUmur() {
        const nilai = inputUmur.value.trim();

        if (nilai === '') {
            ageWarning.style.display = 'none';
            ageWarning.textContent = '';
            return;
        }

        const umur = Number(nilai);
        if (!Number.isInteger(umur) || umur < 17 || umur > 19) {
            ageWarning.textContent = 'Peringatan: umur harus 17–19 tahun. Formulir tidak akan lolos validasi jika di luar rentang tersebut.';
            ageWarning.style.color = '#ffc36d';
            ageWarning.style.display = 'block';
        } else {
            ageWarning.textContent = 'Umur memenuhi persyaratan lomba.';
            ageWarning.style.color = '#8ce0b4';
            ageWarning.style.display = 'block';
        }
    }

    inputUmur.addEventListener('input', periksaUmur);
    periksaUmur();
</script>
</body>
</html>
