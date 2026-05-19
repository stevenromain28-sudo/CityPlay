<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CityPlay - Votre code de sécurité 2FA</title>
    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #F0F7FF;
            margin: 0;
            padding: 40px 20px;
            color: #1E293B;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(29, 161, 242, 0.05);
            border: 1px solid #E2E8F0;
        }
        .header {
            background: linear-gradient(135deg, #1DA1F2 0%, #0077C2 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            font-size: 32px;
            font-weight: 900;
            font-style: italic;
            text-transform: uppercase;
            letter-spacing: -1px;
            color: #FFFFFF;
            margin: 0 0 10px 0;
            font-family: 'Impact', 'Bangers', sans-serif;
        }
        .subtitle {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #E0F2FE;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .greeting {
            font-size: 20px;
            font-weight: 800;
            color: #0F172A;
            margin: 0 0 16px 0;
        }
        .description {
            font-size: 16px;
            line-height: 1.6;
            color: #475569;
            margin: 0 0 32px 0;
        }
        .code-container {
            background-color: #F8FAFC;
            border: 2px dashed #CBD5E1;
            border-radius: 16px;
            padding: 24px;
            margin: 0 auto 32px auto;
            max-width: 280px;
        }
        .code {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: 6px;
            color: #1DA1F2;
            margin: 0;
            line-height: 1;
        }
        .expiry {
            font-size: 13px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .warning {
            font-size: 12px;
            line-height: 1.5;
            color: #94A3B8;
            border-top: 1px solid #F1F5F9;
            padding-top: 24px;
            margin: 0;
        }
        .footer {
            background-color: #F8FAFC;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #F1F5F9;
            font-size: 12px;
            color: #94A3B8;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="logo">CITYPLAY</h1>
            <p class="subtitle">Sécurité & Exploration</p>
        </div>
        <div class="content">
            <h2 class="greeting">Bonjour !</h2>
            <p class="description">
                Une tentative de connexion à votre compte CityPlay a été initiée. Pour valider votre identité, veuillez utiliser le code de sécurité temporaire ci-dessous :
            </p>
            <div class="code-container">
                <h3 class="code">{{ $code }}</h3>
            </div>
            <p class="expiry">Ce code est valide pendant 15 minutes.</p>
            <p class="warning">
                Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité. Nous vous conseillons néanmoins de modifier votre mot de passe par précaution.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CityPlay. Tous droits réservés.
        </div>
    </div>
</body>
</html>
