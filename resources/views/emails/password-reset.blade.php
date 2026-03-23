<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Sprout Password</title>
</head>
<body style="margin:0;padding:0;background:#f4fbf5;font-family:Inter,Arial,sans-serif;color:#1e2a24;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4fbf5;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 16px 40px rgba(15,46,28,0.08);">
                    <tr>
                        <td style="background:linear-gradient(180deg,#dff9e5 0%,#ffffff 100%);padding:32px 32px 20px;text-align:center;">
                            <img src="{{ $logoUrl }}" alt="Sprout" style="width:104px;height:auto;display:block;margin:0 auto 18px;">
                            <div style="font-family:'Inknut Antiqua',Georgia,serif;font-size:26px;line-height:1.2;color:#0d2f19;font-weight:700;">Sprout</div>
                            <div style="margin-top:10px;font-size:14px;line-height:1.6;color:#4f6258;">
                                Secure password reset request
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 32px 36px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.7;color:#22352c;">
                                Dear {{ $user->name ?? 'Sprout User' }},
                            </p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.8;color:#50635a;">
                                We received a request to reset the password for your Sprout account. To continue, please click the button below and create a new password.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0;">
                                <tr>
                                    <td align="center" style="border-radius:999px;background:#00d95f;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 26px;border-radius:999px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">
                                            Create New Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#50635a;">
                                This password reset link will expire in {{ $expirationMinutes }} minutes for your security.
                            </p>
                            <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#50635a;">
                                If you did not request a password reset, no further action is needed and your current password will remain unchanged.
                            </p>
                            <p style="margin:24px 0 0;font-size:13px;line-height:1.8;color:#7a8c83;word-break:break-all;">
                                If the button does not work, copy and paste this link into your browser:<br>
                                <a href="{{ $resetUrl }}" style="color:#00b957;text-decoration:none;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
