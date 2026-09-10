<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDM verification code</title>
</head>
<body style="margin:0;background:#f5f5f0;font-family:Arial,sans-serif;color:#162033;">
    <div style="max-width:560px;margin:32px auto;background:#ffffff;border-left:6px solid #0b5d34;border-right:6px solid #f3cf00;padding:32px;box-sizing:border-box;">
        <p style="margin:0 0 8px;color:#0b5d34;font-size:13px;font-weight:700;letter-spacing:1px;">COLEGIO DE MONTALBAN</p>
        <h1 style="margin:0 0 16px;font-size:24px;">Your verification code</h1>
        <p style="margin:0 0 24px;line-height:1.6;">
            {{ $purpose === 'registration' ? 'Use this code to finish creating your student account.' : 'Use this code to finish signing in to your student account.' }}
        </p>
        <div style="margin:0 0 24px;padding:18px;background:#f6f8f6;border:1px solid #dfe6df;text-align:center;font-size:32px;font-weight:700;letter-spacing:8px;color:#0b5d34;">
            {{ $otp }}
        </div>
        <p style="margin:0 0 12px;line-height:1.6;">This code expires in {{ $expiresMinutes }} minutes and can only be used once.</p>
        <p style="margin:0;color:#667085;font-size:13px;line-height:1.5;">If you did not request this code, you can safely ignore this email. Never share the code with anyone.</p>
    </div>
</body>
</html>
