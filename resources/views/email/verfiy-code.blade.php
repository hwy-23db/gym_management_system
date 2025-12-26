<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;">
    <div style="max-width:500px; margin:auto; background:#ffffff; padding:20px; border-radius:6px;">
        <h2 style="text-align:center;">Verify Your Email</h2>

        <p>Use the verification code below to activate your account:</p>

        <h1 style="text-align:center; letter-spacing:4px;">
            {{ $code }}
        </h1>

        <p style="color:#555;">
            This code will expire in <strong>10 minutes</strong>.
        </p>

        <p>If you didn’t create this account, just ignore this email.</p>

        <p style="margin-top:30px;">— Your App Team</p>
    </div>
</body>
</html>
