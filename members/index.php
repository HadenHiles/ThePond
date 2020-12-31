<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Pond Member Counter</title>

    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Oswald" />
    <link rel="stylesheet" type="text/css" href="./dist/style.css" />
</head>

<body>
    <div class="clock">
        <div class="logo-wrapper">
            <div class="logo">
                <img src="https://cdn.thepond.howtohockey.com/2020/01/THEPOND_RGB_WHITE_WORDMARK_RAW.svg" />
            </div>
        </div>

        <div class="digit six">
            <span class="base"></span>
            <div class="flap over front"></div>
            <div class="flap over back"></div>
            <div class="flap under"></div>
        </div>

        <div class="digit five">
            <span class="base"></span>
            <div class="flap over front"></div>
            <div class="flap over back"></div>
            <div class="flap under"></div>
        </div>

        <div class="digit four">
            <span class="base"></span>
            <div class="flap over front"></div>
            <div class="flap over back"></div>
            <div class="flap under"></div>
        </div>

        <div class="digit three">
            <span class="base"></span>
            <div class="flap over front"></div>
            <div class="flap over back"></div>
            <div class="flap under"></div>
        </div>

        <div class="digit two">
            <span class="base"></span>
            <div class="flap over front"></div>
            <div class="flap over back"></div>
            <div class="flap under"></div>
        </div>

        <div class="digit one">
            <span class="base"></span>
            <div class="flap over front"></div>
            <div class="flap over back"></div>
            <div class="flap under"></div>
        </div>

        <div class="indicator">
            <h1>Members</h1>
        </div>
    </div>

    <audio id="new-member-sound" src="./goal-horn.mp3"></audio>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript" src="counter.js"></script>
</body>

</html>