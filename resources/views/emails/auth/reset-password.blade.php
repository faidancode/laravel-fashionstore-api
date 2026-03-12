<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h2>Halo, {{ $name }}</h2>
    <p>Anda meminta reset password. Gunakan token berikut untuk melanjutkan proses:</p>
    <p><strong>Token:</strong> {{ $token }}</p>
    <p>Token berlaku sampai {{ $expiresAt->format('d M Y H:i') }}.</p>
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
</body>
</html>
