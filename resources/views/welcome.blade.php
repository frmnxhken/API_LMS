<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUHAKKU | Nothing</title>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #0a0a0a;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            text-align: center;
        }

        .container {
            padding: 20px;
            max-width: 90%;
            animation: fadeIn 2s ease-in;
        }

        h1 {
            font-size: clamp(3rem, 10vw, 6rem);
            letter-spacing: -2px;
            margin: 0;
            background: linear-gradient(to right, #ffffff, #444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: clamp(1rem, 3vw, 1.5rem);
            color: #777;
            margin-top: 15px;
            font-style: italic;
            line-height: 1.5;
            max-width: 600px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dot {
            height: 8px;
            width: 8px;
            background-color: #333;
            border-radius: 50%;
            display: inline-block;
            margin-top: 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.5);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 0.5;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Kuhakku.</h1>
        <p>"Hidup hanyalah permainan sampah. Tidak peduli bagaimana kamu memainkannya, tidak ada cara untuk menang."</p>
        <div class="dot"></div>
    </div>

</body>

</html>