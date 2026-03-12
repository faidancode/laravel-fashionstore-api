<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Email</title>
</head>
<body>
    <h2>Halo, {{ $name }}</h2>
    <p>Terima kasih sudah mendaftar. Silakan konfirmasi email Anda menggunakan informasi berikut:</p>
    <p><strong>PIN:</strong> {{ $pin }}</p>
    <p><strong>Token:</strong> {{ $token }}</p>
    <p>Token berlaku sampai {{ $expiresAt->format('d M Y H:i') }}.</p>
    <p>Jika Anda tidak merasa melakukan pendaftaran, abaikan email ini.</p>
</body>
</html>
