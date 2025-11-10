<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('dashboard.OTP Verification Code') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            direction: rtl;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .email-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .message {
            font-size: 16px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .otp-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #666666;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .otp-expiry {
            font-size: 14px;
            color: #999999;
            margin-top: 15px;
        }
        .warning-box {
            background-color: #fff3cd;
            border-right: 4px solid #ffc107;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 25px 0;
        }
        .warning-box p {
            font-size: 14px;
            color: #856404;
            margin: 0;
            line-height: 1.5;
        }
        .info-box {
            background-color: #e7f3ff;
            border-right: 4px solid #2196F3;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 25px 0;
        }
        .info-box p {
            font-size: 14px;
            color: #0c5460;
            margin: 0;
            line-height: 1.5;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .email-footer p {
            font-size: 14px;
            color: #666666;
            margin: 5px 0;
            line-height: 1.6;
        }
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e9ecef, transparent);
            margin: 30px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 8px;
            }
            .email-header {
                padding: 30px 20px;
            }
            .email-header h1 {
                font-size: 24px;
            }
            .email-body {
                padding: 30px 20px;
            }
            .otp-code {
                font-size: 36px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ __('dashboard.Verification Code') }}</h1>
            <p>{{ __('dashboard.Your OTP Code') }}</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ __('dashboard.Hello') }} {{ $user->first_name }}{{ $user->last_name ? ' ' . $user->last_name : '' }},
            </div>

            <div class="message">
                {{ __('dashboard.OTP Email Message') }}
            </div>

            <!-- OTP Container -->
            <div class="otp-container">
                <div class="otp-label">{{ __('dashboard.Your Verification Code') }}</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expiry">
                    {{ __('dashboard.This code will expire in') }} <strong>10 {{ __('dashboard.minutes') }}</strong>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <p>
                    <strong>{{ __('dashboard.Security Notice') }}:</strong> {{ __('dashboard.OTP Security Warning') }}
                </p>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <p>
                    <strong>{{ __('dashboard.Note') }}:</strong> {{ __('dashboard.OTP Info Message') }}
                </p>
            </div>

            <div class="divider"></div>

            <div class="message" style="font-size: 14px; color: #999999; margin-top: 20px;">
                {{ __('dashboard.If you did not request this code, please ignore this email.') }}
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>{{ __('dashboard.Thank you') }}</strong></p>
            <p>{{ __('dashboard.Email Footer Message') }}</p>
            <p style="margin-top: 20px; font-size: 12px; color: #999999;">
                © {{ date('Y') }} {{ config('app.name') }}. {{ __('dashboard.All rights reserved') }}.
            </p>
        </div>
    </div>
</body>
</html>
