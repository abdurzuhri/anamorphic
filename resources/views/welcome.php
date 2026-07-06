<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anamorphic Framework</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Halant:wght@400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #000;
        }

        body {
            font-family: 'Halant', serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #fff;
        }

        .stage {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 22px;
        }

        .brand img {
            height: 64px;
            width: auto;
            filter: invert(1);
        }

        .brand span {
            font-size: 56px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .meta {
            margin-top: 18px;
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 400;
            color: #d8d8d8;
            letter-spacing: 0.3px;
        }

        .meta .sep {
            margin: 0 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="stage">
        <div class="brand">
            <img src="/assets/anamorphic-logo.svg" alt="Anamorphic" style="height: 60px;">
        </div>
        <div class="meta">
            v1.0<span class="sep">|</span>framework
        </div>
    </div>
</body>
</html>
