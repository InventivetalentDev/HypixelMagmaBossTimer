<!DOCTYPE html>
<html>
    <head>
        <title>Hypixel Skyblock Magma Boss Timer</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/fontawesome.min.css" integrity="sha256-AaQqnjfGDRZd/lUp0Dvy7URGOyRsh8g9JdWUkyYxNfI=" crossorigin="anonymous"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/brands.min.css" integrity="sha256-u8123o+sLy8uk0Du9H0Ub+KinAoHanzGsBqDkWHY1f8=" crossorigin="anonymous"/>

        <style>
            html, body, .the-wrapper {
                height: 100%;
                overflow: hidden;
            }

            body, .modal-content {
                background-color: rgb(55, 40, 47);
            }

            body {
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


            #btnOverlay {
                position: fixed;
                top: 5px;
                right: 5px;
            }

            #socialButtons {
                font-size: 32px;
            }

            #socialButtons > a {
                margin-left: 4px;
            }
        </style>


        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div id="bgImage"></div>

        <div id="btnOverlay">
            <a href="#timelineModal" id="historyLink" class="modal-trigger"><i class="material-icons">trending_up</i></a>
            <a href="#infoModal" id="infoLink" class="modal-trigger"><i class="material-icons">info_outline</i></a>
        </div>

        <div id="content" class="container">

            <div class="center-align">
                <div class="row center-align">
                    <h5 class="center-align">The Magma Boss should spawn in about</h5>
                    <h1 class="center-align" id="time">00:00:00</h1>
                    <span id="nextTime"></span>
                    <br/>
                    <h4 class="center-align" id="suggestionMessage"></h4>
                    <br/>
                    <br/>
                    <span>Last tracked spawn was <span id="lastTrackedSpawn"></span></span>
                </div>

                <br/>
                <br/>

                <div class="row center-align">
                    <strong>NOTE: Please click the buttons below <i>only</i> if the events actually occurred!</strong><br/>
                    <span>They will update the timer for <b>everyone</b>!</span><br/>
                    <button disabled class="btn center-align track-btn amber" id="waveBlazeBtn">
                        Blaze Wave Spawned <span id="waveBlazeTime"></span>
                    </button>
                    <br/>
                    <button disabled class="btn center-align track-btn deep-orange" id="waveMagmaBtn">
                        Magma Wave Spawned <span id="waveMagmaTime"></span>
                    </button>
                    <br/>
                    <button disabled class="btn center-align track-btn purple darken-3" id="musicBtn">
                        Mysterious Music Playing <span id="musicTime"></span>
                    </button>
                    <br/>
                    <button disabled class="btn center-align track-btn red darken-4" id="spawnedBtn">
                        Magma Boss Spawned
                    </button>
                </div>
            </div>

        </div>

        <span style="position: fixed; bottom: 4px; left: 4px; color: gray;">Created by <a target="_blank" href="https://inventivetalent.org/?utm_source=hypixel_magma_tracker">inventivetalent</a></span>


        <div id="infoModal" class="modal">
            <div class="modal-content">
                <h4>About</h4>
                <p>
                    <a href="https://hypixel.net/threads/magma-boss-timer-app.2238543/" target="_blank">This tool</a> can easily track the spawn times and related events of the Magma Boss on Hypixel's Skyblock.<br/>
                    You simply check the timer, wait for the boss to spawn and click the according buttons whenever one of the events or the spawn itself occurs.
                    The timer will update accordingly and display the estimated spawn time, based on the known delays between events.<br/>
                    This is meant to help everyone. Please don't abuse it by submitting false information. Thanks! :)<br/>
                    <br/>
                    There are currently <strong><span id="activeUserCount">0</span></strong> users watching this timer!
                    <br/>
                </p>
                <div class="divider"></div>
                <br/>
                <div class="row">
                    <form class="col s12">
                        <div class="row">
                            <div class="input-field col s12">
                                <span>10 Minute Notification</span>
                                <div class="switch">
                                    <label>
                                        Off
                                        <input type="checkbox" id="tenMinNotificationSwitch">
                                        <span class="lever"></span>
                                        On
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="divider"></div>
                <br/>
                <span id="socialButtons">
                    <a href="https://twitter.com/Inventivtalent"><i class="fab fa-twitter"></i></a>
                    <a href="https://yeleha.co/discord"><i class="fab fa-discord"></i></a>
                    <a href="https://donation.inventivetalent.org/"><i class="fab fa-patreon"></i></a>
                    <a href="https://github.com/InventivetalentDev/HypixelMagmaBossTimer"><i class="fab fa-github"></i></a>
                </span>

            </div>
        </div>

        <div id="timelineModal" class="modal bottom-sheet">
            <div class="modal-content">
                <h4>Timeline</h4>

                <div id="timelineChart"></div>

                <button class="btn" id="timelineLoadMore">&lt; Load More</button>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
        <script src="https://www.google.com/recaptcha/api.js?render=6LeaYLIUAAAAAHfC2C6GsI84CW5sJjuaZA9FERRE"></script>
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/timeline.js"></script>
        <script>
            $(document).ready(function () {
                console.log(
                    "================================================================\n" +
                    "   Hey there o/\n" +
                    "   Found a bug or wanna contribute to this project?\n" +
                    "   Awesome, it's on GitHub!\n" +
                    "   https://github.com/InventivetalentDev/HypixelMagmaBossTimer\n" +
                    "================================================================\n");

                // Modal init
                $('#infoModal').modal();
                $('#timelineModal').modal({
                    onOpenEnd: function () {
                        makeTimelineChart();
                    }
                })

                let reCaptchaToken = null;
                grecaptcha.ready(function () {
                    console.log("recaptcha ready");

                    $(".track-btn").attr("disabled", false);
                });

                let bgIndex = getRndInteger(1, 10);
                $("#bgImage").css("background-image", "url(img/bg/" + bgIndex + ".jpg), url(img/bg/" + bgIndex + ".png)");

                let now = new Date().getTime();
                let twoHoursInMillis = 7.2e+6;
                let oneAndHalfHourInMillis = 5.4e+6;
                let twentyMinsInMillis = 1.2e+6;
                let tenMinsInMillis = 600000;
                let fiveMinsInMillis = 300000;

                let tenMinuteNotification;

                let estimateData = {};

                let timerId = -1;
                let historyHours = 4;
                let lastFocused = now / 1000;

                function updateTimer() {
                    now = Date.now();

                    let hoursSinceLastSpawn = moment.duration(now - estimateData.latest.spawn).hours();

                    $("#nextTime").text("(" + moment(estimateData.estimate).format('MMMM Do YYYY, h:mm:ss a') + ")");
                    $("#lastTrackedSpawn").html(moment(estimateData.latest.spawn).fromNow() + "<br/> (" + moment(estimateData.latest.spawn).format('MMMM Do YYYY, h:mm:ss a') + ")" + (hoursSinceLastSpawn > 5 ? "<br/><i>The timer could likely be inaccurate, since server restarts etc. are not accounted for</i>" : "") + "");

                    let duration = estimateData.estimate - now;
                    let formattedTimer = moment.utc(duration).format("HH:mm:ss");
                    $("#time").text(formattedTimer);
                    $('head title', window.parent.document).text(formattedTimer + " | Hypixel Skyblock Magma Boss Timer");

                    if (now - estimateData.latest.blaze < twentyMinsInMillis) {
                        $("#waveBlazeTime").text("(" + moment(estimateData.latest.blaze).fromNow() + ")");
                        // $("#waveBlazeBtn").attr("disabled", true);
                    } else {
                        $("#waveBlazeTime").text("");
                    }
                    if (now - estimateData.latest.magma < tenMinsInMillis) {
                        $("#waveMagmaTime").text("(" + moment(estimateData.latest.magma).fromNow() + ")");
                        // $("#waveMagmaBtn").attr("disabled", true);
                    } else {
                        $("#waveMagmaTime").text("");
                    }
                    if (now - estimateData.latest.music < fiveMinsInMillis) {
                        $("#musicTime").text("(" + moment(estimateData.latest.music).fromNow() + ")");
                        // $("#musicBtn").attr("disabled", true);
                    } else {
                        $("#musicTime").text("");
                    }


                    let message = "";
                    if (duration < tenMinsInMillis) {
                        message = "If you're not already in the Nether Fortress, you should get going!";

                        if (localStorage.getItem("tenMinNotification") === "true") {
                            if (!tenMinuteNotification) {
                                tenMinuteNotification = showNotification("The Skyblock Magma Boss should spawn in less than 10 minutes!");
                            }
                        }
                    } else {
                        tenMinuteNotification = null;
                    }
                    if (duration < fiveMinsInMillis) {
                        message = "Get ready!";
                    }
                    $("#suggestionMessage").text(message);
                }

                function refreshEstimate() {
                    $.ajax("get_estimated_spawn.php").done(function (data) {
                        console.log(data);
                        estimateData = data;


                        updateTimer();
                        clearInterval(timerId);
                        timerId = setInterval(updateTimer, 1000);// tick every second
                    });

                    $.ajax("get_active_users.php").done(function (data) {
                        $("#activeUserCount").text(data);
                    })
                }

                refreshEstimate();
                setInterval(refreshEstimate, 60000);// update estimate every minute

                function ping() {
                    $.ajax({
                        method: "POST",
                        url: "ping.php",
                        data:{
                            lastFocused: Math.floor(lastFocused)
                        }
                    })
                }

                ping();
                setInterval(ping, 30000);


                $("#waveBlazeBtn").click(function () {
                    let $this = $(this);
                    confirmAndCaptchaAdd("a blaze wave", function (b) {
                        if (b) {
                            $this.attr("disabled", true);
                            $.ajax({
                                method: "POST",
                                url: "add_event.php",
                                data: {type: "blaze", captcha: reCaptchaToken}
                            }).done(function () {
                                // $this.css("display", "none");
                                $this.attr("disabled", true);

                                refreshEstimate()
                            })
                        }
                    })
                });

                $("#waveMagmaBtn").click(function () {
                    let $this = $(this);
                    confirmAndCaptchaAdd("a magma wave", function (b) {
                        if (b) {
                            $this.attr("disabled", true);
                            $.ajax({
                                method: "POST",
                                url: "add_event.php",
                                data: {type: "magma", captcha: reCaptchaToken}
                            }).done(function () {
                                // $this.css("display", "none");
                                $this.attr("disabled", true);

                                refreshEstimate();
                            })
                        }
                    })
                });

                $("#musicBtn").click(function () {
                    let $this = $(this);
                    confirmAndCaptchaAdd("music", function (b) {
                        if (b) {
                            $this.attr("disabled", true);
                            $.ajax({
                                method: "POST",
                                url: "add_event.php",
                                data: {type: "music", captcha: reCaptchaToken}
                            }).done(function () {
                                // $this.css("display", "none");
                                $this.attr("disabled", true);

                                refreshEstimate();
                            })
                        }
                    })
                });

                $("#spawnedBtn").click(function () {
                    let $this = $(this);
                    confirmAndCaptchaAdd("a boss spawn", function (b) {
                        if (b) {
                            $this.attr("disabled", true);
                            $.ajax({
                                method: "POST",
                                url: "add_event.php",
                                data: {type: "spawn", captcha: reCaptchaToken}
                            }).done(function () {
                                // $this.css("display", "none");
                                $this.attr("disabled", true);

                                // refreshEstimate();
                            })
                        }
                    })
                });

                $("#tenMinNotificationSwitch").prop("checked", localStorage.getItem("tenMinNotification") === "true");
                $("#tenMinNotificationSwitch").change(function () {
                    let checked = $(this).is(":checked");
                    if (checked) {
                        Notification.requestPermission().then(function (result) {
                            console.log(result);
                            localStorage.setItem("tenMinNotification", "true");
                        });
                    } else {
                        localStorage.setItem("tenMinNotification", "false");
                    }
                });


                function makeTimelineChart() {
                    $.ajax("history_chart.php?hours=" + historyHours).done(function (data) {
                        Highcharts.chart('timelineChart', {
                            chart: {
                                zoomType: 'x',
                                type: 'timeline',
                                height: '20%',
                                backgroundColor: "rgb(55, 40, 47)",
                                marginLeft: 80,
                                marginRight: 80
                            },
                            xAxis: {
                                type: 'datetime',
                                visible: false
                            },
                            yAxis: {
                                gridLineWidth: 1,
                                title: null,
                                labels: {
                                    enabled: false
                                }
                            },
                            legend: {
                                enabled: false
                            },
                            title: {
                                text: null
                            },
                            tooltip: {
                                style: {
                                    //width: 300
                                },
                                //TODO: use formatter function in order to display extra data
                                headerFormat: '<span style="color:{point.color}">\u25CF</span> {point.key}<br/>',
                                pointFormat: '<span>{point.x:%y-%m-%d} <b>{point.x:%H:%M:%S}</b></span><br/>',
                                footerFormat: ''
                            },
                            series: [{
                                dataLabels: {
                                    allowOverlap: false,
                                    /* format: '<span style="color:{point.color}">● </span><span style="font-weight: bold;" > ' +
                                         '{point.x:%d %b %Y}</span><br/>{point.name}'*/
                                },
                                marker: {
                                    symbol: 'circle'
                                },
                                data: data
                            }]
                        });
                    });
                }

                $("#timelineLoadMore").click(function () {
                    if (historyHours < 24) {
                        historyHours += 2;
                    } else {
                        $(this).attr("disabled", true);
                    }
                    makeTimelineChart();
                });

                $(window).on("focus blur", function () {
                    lastFocused = Date.now() / 1000;
                });

                function showNotification(body, title) {
                    if (!("Notification" in window)) {
                        console.warn("Browser does not support notifications");
                        return;
                    }
                    if (Notification.permission !== "granted") {
                        console.warn("Notifications not granted");
                        return;
                    }

                    return new Notification(title || "Magma Boss Reminder", {
                        body: body,
                        icon: "https://hypixel.inventivetalent.org/img/Magma_Cube_50px.png"
                    });
                }


                function confirmAndCaptchaAdd(type, cb) {
                    function checkCaptcha() {
                        grecaptcha.execute('6LeaYLIUAAAAAHfC2C6GsI84CW5sJjuaZA9FERRE', {action: (type || "homepage").replace(/ /gi, "_")}).then(function (token) {
                            console.log("got recaptcha token");
                            reCaptchaToken = token;

                            cb(true);
                        });
                        return true;
                    }

                    if (estimateData.estimate - now > 5.4e+6) {
                        if (confirm("The next estimated spawn phase has not yet started. Are you sure you want add " + (type || "a spawn") + "?") == true) {
                            return checkCaptcha();
                        }
                        return false;
                    }
                    return checkCaptcha()
                }

                function getRndInteger(min, max) {
                    return Math.floor(Math.random() * (max - min)) + min;
                }
            })
        </script>
    </body>
</html>