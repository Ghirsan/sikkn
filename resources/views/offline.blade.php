<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Offline - SIKKN</title>
    <meta name="theme-color" content="#171717" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #171717;
            color: #fafafa;
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 24rem;
        }
        .icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.5rem;
            opacity: 0.5;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #fafafa;
        }
        p {
            font-size: 0.875rem;
            line-height: 1.5;
            color: #a3a3a3;
            margin-bottom: 2rem;
        }
        button {
            background: #262626;
            color: #fafafa;
            border: 1px solid #404040;
            padding: 0.625rem 1.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s;
        }
        button:hover { background: #404040; }
        button:active { background: #525252; }
    </style>
</head>
<body>
    <div class="container">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <h1>Tidak Ada Koneksi</h1>
        <p>Anda sedang offline. Periksa koneksi internet Anda dan coba lagi.</p>
        <button onclick="window.location.reload()">Coba Lagi</button>
    </div>
</body>
</html>
