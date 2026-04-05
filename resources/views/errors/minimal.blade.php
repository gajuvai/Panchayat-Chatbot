{{--
    Ultra-minimal fallback — used when even the layout may fail
    (e.g. DB connection error prevents Vite manifest from loading)
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error {{ $code ?? 500 }} — Panchayat</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; text-align: center; }
        .card { background: white; border-radius: 1rem; border: 1px solid #e2e8f0; padding: 3rem 2rem; max-width: 28rem; width: 100%; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .code { font-size: 5rem; font-weight: 900; color: #e2e8f0; line-height: 1; }
        h1 { font-size: 1.25rem; font-weight: 700; margin: .75rem 0 .5rem; }
        p { color: #64748b; font-size: .875rem; line-height: 1.6; margin-bottom: 1.5rem; }
        .btn { display: inline-flex; align-items: center; gap: .5rem; background: #4f46e5; color: white; padding: .625rem 1.25rem; border-radius: .75rem; text-decoration: none; font-size: .875rem; font-weight: 500; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">{{ $code ?? 500 }}</div>
        <h1>{{ $title ?? 'Something went wrong' }}</h1>
        <p>{{ $message ?? 'An unexpected error occurred. Please try again or return home.' }}</p>
        <a href="/" class="btn">Go to Home</a>
    </div>
</body>
</html>
