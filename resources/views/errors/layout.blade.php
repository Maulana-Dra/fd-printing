<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('error_code', 'Error') — {{ config('app.name') }}</title>
    <meta name="description" content="@yield('error_message', 'Terjadi kesalahan')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 30%, #fed7aa 60%, #fdba74 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(234,88,12,0.15), 0 0 0 1px rgba(255,255,255,0.8);
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #f97316, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        .error-emoji { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .error-title {
            font-size: 1.375rem;
            font-weight: 700;
            color: #1c1917;
            margin-bottom: 0.75rem;
        }
        .error-desc {
            color: #78716c;
            line-height: 1.6;
            font-size: 0.9375rem;
            margin-bottom: 2rem;
        }
        .btn-group { display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            box-shadow: 0 4px 12px rgba(234,88,12,0.35);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(234,88,12,0.45); }
        .btn-secondary {
            background: #f5f5f4;
            color: #44403c;
            border: 1px solid #e7e5e4;
        }
        .btn-secondary:hover { background: #e7e5e4; }
        .divider { height: 1px; background: #f0f0ef; margin: 1.5rem 0; }
        .help-text { font-size: 0.8125rem; color: #a8a29e; }
        .help-text a { color: #f97316; text-decoration: none; }
        .help-text a:hover { text-decoration: underline; }
        @media (max-width: 480px) {
            .card { padding: 2rem 1.5rem; }
            .error-code { font-size: 4.5rem; }
            .btn-group { flex-direction: column; }
            .btn { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="error-emoji">@yield('emoji', '⚠️')</span>
        <div class="error-code">@yield('error_code', '???')</div>
        <h1 class="error-title">@yield('error_title', 'Terjadi Kesalahan')</h1>
        <p class="error-desc">@yield('error_desc', 'Mohon maaf, terjadi kesalahan yang tidak terduga.')</p>

        <div class="btn-group">
            <a href="{{ url('/') }}" class="btn btn-primary">
                🏠 Kembali ke Beranda
            </a>
            @yield('extra_actions')
        </div>

        <div class="divider"></div>

        <p class="help-text">
            Butuh bantuan? Hubungi kami via
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('printing.company.phone', '')) }}">WhatsApp</a>
            atau email <a href="mailto:{{ config('printing.company.email', 'cs@fdprinting.id') }}">{{ config('printing.company.email', 'cs@fdprinting.id') }}</a>
        </p>
    </div>
</body>
</html>
