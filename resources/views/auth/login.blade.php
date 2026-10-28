<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Sensus Ekonomi Desa</title>
    <style>
    :root {
        --hijau: #0f766e;
        --hijau-tua: #134e4a;
        --teks: #1f2937;
        --abu: #6b7280;
        --garis: #d1d5db;
        --bahaya: #b91c1c;
        --bahaya-bg: #fee2e2
    }

    * {
        box-sizing: border-box
    }

    body {
        margin: 0;
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 20px;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        font-size: 15px;
        color: var(--teks);
        background: linear-gradient(135deg, var(--hijau-tua), var(--hijau))
    }

    .kotak {
        width: 100%;
        max-width: 400px;
        background: #fff;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, .25)
    }

    .logo {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: var(--hijau);
        color: #fff;
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 18px;
        margin: 0 auto 14px
    }

    h1 {
        font-size: 20px;
        text-align: center;
        margin: 0
    }

    .sub {
        text-align: center;
        color: var(--abu);
        font-size: 13px;
        margin: 4px 0 24px
    }

    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px
    }

    input {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid var(--garis);
        border-radius: 8px;
        font-size: 15px;
        margin-bottom: 16px
    }

    input:focus {
        outline: 2px solid var(--hijau);
        border-color: transparent
    }

    button {
        width: 100%;
        padding: 12px;
        border: 0;
        border-radius: 8px;
        background: var(--hijau);
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer
    }

    button:hover {
        background: var(--hijau-tua)
    }

    .galat {
        background: var(--bahaya-bg);
        color: var(--bahaya);
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 16px
    }

    .petunjuk {
        margin-top: 20px;
        padding: 12px;
        background: #f1f5f4;
        border-radius: 8px;
        font-size: 12px;
        color: var(--abu)
    }

    .petunjuk b {
        color: var(--teks)
    }
    </style>
</head>

<body>
    <div class="kotak">
        <div class="logo">SD</div>
        <h1>Sensus Ekonomi Desa</h1>
        <p class="sub">Masuk untuk melanjutkan</p>

        @if ($errors->any())
        <div class="galat">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" autofocus
                autocomplete="username">

            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password">

            <button type="submit">Masuk</button>
        </form>

        <div class="petunjuk">
            <b>Mode tampilan:</b> isi username <b>admin</b>, <b>petugas</b>, atau <b>pimpinan</b>.
            Password bebas, belum diperiksa sampai tahap login sungguhan dibuat.
        </div>
    </div>
</body>

</html>