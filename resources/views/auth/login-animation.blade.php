<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8fafc;
            overflow: hidden;
        }

        .animation-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .logo-wrapper {
            width: 120px;
            height: 120px;
            animation: logoAnimation 2s ease-out forwards;
        }

        .logo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        @keyframes logoAnimation {
            0% {
                transform: scale(0.01);
                opacity: 0;
            }
            50% {
                transform: scale(1.5);
                opacity: 1;
            }
            100% {
                transform: scale(10);
                opacity: 0;
            }
        }

        /* 背景のフェード効果 */
        .fade-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0);
            animation: fadeOverlay 2s ease-out forwards;
            z-index: 9998;
        }

        @keyframes fadeOverlay {
            0% {
                background-color: rgba(255, 255, 255, 0);
            }
            70% {
                background-color: rgba(255, 255, 255, 0.3);
            }
            100% {
                background-color: rgba(255, 255, 255, 1);
            }
        }
    </style>
</head>
<body>
    <div class="fade-overlay"></div>
    <div class="animation-container">
        <div class="logo-wrapper">
            <img src="{{ asset('images/SJT_LOGO.svg') }}" alt="SJT Logo">
        </div>
    </div>

    <script>
        // 2秒後に指定されたURLへリダイレクト
        setTimeout(function() {
            window.location.href = "{{ $redirectUrl }}";
        }, 2000);
    </script>
</body>
</html>