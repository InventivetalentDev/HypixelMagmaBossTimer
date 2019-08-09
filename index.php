<!DOCTYPE html>
<html>
    <head>
        <title>Hypixel Skyblock Magma Boss Timer</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

        <style>
            html, body, .the-wrapper {
                height: 100%;
                overflow: hidden;
            }

            body {
                background-color: rgb(55, 40, 47);
                color: #e9e9e9;
            }

            #bgImage {
                height: 100%;
                filter: blur(6px);
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
            }

            #content {
                position: absolute;

                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);

                z-index: 2;
            }

            .btn {
                margin: 3px;
            }


            #time {
                font-size: 5rem;
                margin: 0;
            }
        </style>

        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div id="bgImage"></div>

        <div id="content" class="container">

            <div class="center-align">
                <div class="row center-align">
                    <h5 class="center-align">The Magma Boss should spawn in about</h5>
                    <h1 class="center-align" id="time">00:00:00</h1>
                    <span id="nextTime"></span>
                    <br/>
                    <br/>
                    <span>Last tracked spawn was <span id="lastTrackedSpawn"></span></span>
                </div>

                <br/>
                <br/>
                <br/>
                <br/>

                <div class="row center-align">
                    <button disabled class="btn center-align track-btn amber" id="waveBlazeBtn">
                        Blaze Wave Spawned
                    </button>
                    <br/>
                    <button disabled class="btn center-align track-btn deep-orange" id="waveMagmaBtn">
                        Magma Wave Spawned
                    </button>
                    <br/>
                    <button disabled class="btn center-align track-btn purple darken-3" id="musicBtn">
                        Mysterious Music Playing
                    </button>
                    <br/>
                    <button disabled class="btn center-align track-btn red darken-4" id="spawnedBtn">
                        Magma Boss Spawned
                    </button>
                </div>
            </div>

        </div>


        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
        <script src="https://www.google.com/recaptcha/api.js?render=6LehTrIUAAAAAJOVyw92PrxY0_g80eXmIEJdTk3d"></script>
        <script>
            $(document).ready(function () {

                let reCaptchaToken = null;
                grecaptcha.ready(function () {
                    console.log("recaptcha ready");
                    grecaptcha.execute('6LehTrIUAAAAAJOVyw92PrxY0_g80eXmIEJdTk3d', {action: 'homepage'}).then(function (token) {
                        console.log("got recaptcha token");
                        reCaptchaToken = token;

                        $(".track-btn").attr("disabled", false);
                    });
                });

                let bgIndex = getRndInteger(1, 10);
                $("#bgImage").css("background-image", "url(img/bg/" + bgIndex + ".jpg), url(img/bg/" + bgIndex + ".png)");

                let now = new Date().getTime();
                let twoHoursInMillis = 7.2e+6;
                let twentyMinsInMillis = 1.2e+6;
                let tenMinsInMillis = 600000;
                let fiveMinsInMillis = 300000;

                let estimateData = {};

                let timerId = -1;

                function updateTimer() {
                    now = Date.now();

                    $("#nextTime").text("(" + moment(estimateData.estimate).format('MMMM Do YYYY, h:mm:ss a') + ")");
                    $("#lastTrackedSpawn").text(moment(estimateData.latest.spawn).fromNow() + " (" + moment(estimateData.latest.spawn).format('MMMM Do YYYY, h:mm:ss a') + ")");

                    let duration = estimateData.estimate - now;
                    let formattedTimer = moment.utc(duration).format("HH:mm:ss");
                    $("#time").text(formattedTimer);
                    $('head title', window.parent.document).text(formattedTimer + " | Hypixel Skyblock Magma Boss Timer");
                }

                function refreshEstimate() {
                    $.ajax("get_estimated_spawn.php").done(function (data) {
                        console.log(data);
                        estimateData = data;


                        if (now - data.latest.blaze < twentyMinsInMillis) {
                            $("#waveBlazeBtn").text($("#waveBlazeBtn").text() + " (" + moment(data.latest.blaze).fromNow() + ")");
                        }
                        if (now - data.latest.magma < tenMinsInMillis) {
                            $("#waveMagmaBtn").text($("#waveMagmaBtn").text() + " (" + moment(data.latest.magma).fromNow() + ")");
                        }
                        if (now - data.latest.music < fiveMinsInMillis) {
                            $("#musicBtn").text($("#musicBtn").text() + " (" + moment(data.latest.music).fromNow() + ")");
                        }

                        updateTimer();
                        clearInterval(timerId);
                        timerId = setInterval(updateTimer, 1000);
                    });
                }

                refreshEstimate();


                $("#waveBlazeBtn").click(function () {
                    $(this).attr("disabled", true);
                    $.ajax({
                        method: "POST",
                        url: "add_event.php",
                        data: {type: "blaze",captcha:reCaptchaToken}
                    }).done(function () {
                        $(this).css("display", "none");

                        refreshEstimate();
                    })
                });

                $("#waveMagmaBtn").click(function () {
                    $(this).attr("disabled", true);
                    $.ajax({
                        method: "POST",
                        url: "add_event.php",
                        data: {type: "magma",captcha:reCaptchaToken}
                    }).done(function () {
                        $(this).css("display", "none");

                        refreshEstimate();
                    })
                });

                $("#musicBtn").click(function () {
                    $(this).attr("disabled", true);
                    $.ajax({
                        method: "POST",
                        url: "add_event.php",
                        data: {type: "music",captcha:reCaptchaToken}
                    }).done(function () {
                        $(this).css("display", "none");

                        refreshEstimate();
                    })
                });

                $("#spawnedBtn").click(function () {
                    $(this).attr("disabled", true);
                    $.ajax({
                        method: "POST",
                        url: "add_spawn.php",
                        data: {captcha:reCaptchaToken}
                    }).done(function () {
                        $(this).css("display", "none");

                        // refreshEstimate();
                    })
                });

                function getRndInteger(min, max) {
                    return Math.floor(Math.random() * (max - min)) + min;
                }
            })
        </script>
    </body>
</html>