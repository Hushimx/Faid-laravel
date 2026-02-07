<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>رمز التحقق - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f8fa;
            padding: 20px;
            direction: rtl;
            text-align: right;
        }

        .email-container {
            direction: rtl;
            text-align: right;
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(35, 68, 205, 0.08);
        }

        .email-header {
            background: linear-gradient(135deg, #2344CD 0%, #1a33a3 100%);
            padding: 36px 28px;
            text-align: center;
            color: #ffffff;
        }

        .email-header h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .email-header p {
            font-size: 15px;
            opacity: 0.95;
        }

        .email-body {
            padding: 36px 28px;
        }

        .greeting {
            font-size: 17px;
            color: #464F67;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .message {
            font-size: 15px;
            color: rgba(70, 79, 103, 0.85);
            line-height: 1.7;
            margin-bottom: 24px;
        }

        .otp-container {
            background: rgba(35, 68, 205, 0.06);
            border: 1px solid rgba(35, 68, 205, 0.2);
            border-radius: 12px;
            padding: 28px;
            text-align: center;
            margin: 28px 0;
        }

        .otp-label {
            font-size: 13px;
            color: #464F67;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .otp-code {
            font-size: 40px;
            font-weight: 700;
            color: #2344CD;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
            margin: 12px 0;
            direction: ltr;
            unicode-bidi: embed;
        }

        .otp-expiry {
            font-size: 13px;
            color: rgba(70, 79, 103, 0.6);
            margin-top: 12px;
        }

        .notice-box {
            background-color: rgba(233, 237, 242, 0.8);
            border-right: 4px solid #2344CD;
            border-radius: 8px;
            padding: 14px 18px;
            margin: 22px 0;
        }

        .notice-box p {
            font-size: 14px;
            color: #464F67;
            margin: 0;
            line-height: 1.55;
        }

        .email-footer {
            background-color: #f8f8fa;
            padding: 26px 28px;
            text-align: center;
            border-top: 1px solid rgba(233, 237, 242, 1);
        }

        .email-footer p {
            font-size: 13px;
            color: rgba(70, 79, 103, 0.7);
            margin: 4px 0;
            line-height: 1.6;
        }

        .divider {
            height: 1px;
            background: rgba(233, 237, 242, 1);
            margin: 24px 0;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 12px;
            }
            .email-header {
                padding: 28px 20px;
            }
            .email-header h1 {
                font-size: 22px;
            }
            .email-body {
                padding: 28px 20px;
            }
            .otp-code {
                font-size: 32px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>

<body dir="rtl">
    <div class="email-container">
        <div class="email-header">
            <h1>رمز التحقق</h1>
            <p>استخدم الرمز أدناه لإكمال عملية التحقق</p>
        </div>

        <div class="email-body">
            <div class="greeting">
                مرحباً {{ $user->first_name }}{{ $user->last_name ? ' ' . $user->last_name : '' }}،
            </div>

            <div class="message">
                تلقيت طلباً للتحقق من بريدك الإلكتروني. أدخل الرمز التالي في التطبيق خلال <strong>١٠ دقائق</strong>.
            </div>

            <div class="otp-container">
                <div class="otp-label">رمز التحقق</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expiry">ينتهي صلاحية هذا الرمز بعد ١٠ دقائق</div>
            </div>

            <div class="notice-box">
                <p><strong>ملاحظة أمنية:</strong> لا تشارك هذا الرمز مع أي شخص. فريقنا لن يطلبه منك أبداً عبر الهاتف أو البريد.</p>
            </div>

            <div class="divider"></div>

            <div class="message" style="font-size: 14px; color: rgba(70, 79, 103, 0.6); margin-top: 16px;">
                إذا لم تطلب هذا الرمز، يمكنك تجاهل هذه الرسالة.
            </div>
        </div>

        <div class="email-footer">
            <p><strong>شكراً لاستخدامك {{ config('app.name') }}</strong></p>
            <p style="margin-top: 16px; font-size: 12px; color: rgba(70, 79, 103, 0.5);">
                © {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.
            </p>
        </div>
    </div>
</body>

</html>
