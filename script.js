$(document).ready(function () {
    // Modal init
    $('#infoModal').modal();
    $('#timelineModal').modal({
        onOpenEnd: function () {
            makeTimelineChart();
        }
    });
    $('.track-btn.tooltipped').tooltip({
        position: "left"
    });

    let reCaptchaToken = null;
    grecaptcha.ready(function () {
        console.log("recaptcha ready");

        $(".track-btn").attr("disabled", false);
    });

    let bgIndex = getRndInteger(1, 10);
    $("#bgImage").css("background-image", "url(img/bg/" + bgIndex + ".jpg), url(img/bg/" + bgIndex + ".png)");

    const devMode = window.location.hash === "#DEV";
    if (devMode) {
        $("#d").text("DEV MODE ACTIVE");
    }

    let now = new Date().getTime();
    let twoHoursInMillis = 7.2e+6;
    let oneAndHalfHourInMillis = 5.4e+6;
    let twentyMinsInMillis = 1.2e+6;
    let tenMinsInMillis = 600000;
    let fiveMinsInMillis = 300000;

    let startCounter = 0;

    let tenMinuteNotification;
    let fiveMinuteNotification;

    let estimateData = {};

    let timerId = -1;
    let historyHours = 4;
    let lastFocused = now / 1000;

    let ipv4 = "";
    let ipv6 = "";

    function updateTimer() {
        now = Date.now();

        startCounter++;

        let hoursSinceLastSpawn = moment.duration(now - estimateData.latest.spawn).asHours();
        let hoursSinceLastDeath = moment.duration(now - estimateData.latest.death).asHours();

        let minutesUntilNextSpawn = moment.duration(estimateData.estimate - now).asMinutes();
        let minutesSinceLastSpawn = moment.duration(now - estimateData.latest.spawn).asMinutes();

        let minutesSinceLastBlaze = moment.duration(now - estimateData.latest.blaze).asMinutes();
        let minutesSinceLastMagma = moment.duration(now - estimateData.latest.magma).asMinutes();
        let minutesSinceLastMusic = moment.duration(now - estimateData.latest.music).asMinutes();

        $("#nextTime").text("(" + moment(estimateData.estimate).format('MMMM Do YYYY, h:mm:ss a') + ")");

        let deathMoreRecent = estimateData.latest.death > estimateData.latest.spawn;
        let latestThing = deathMoreRecent ? estimateData.latest.death : estimateData.latest.spawn;
        $("#lastTrackedType").text(deathMoreRecent ? "death" : "spawn");
        $("#lastTrackedTime").html(moment(latestThing).fromNow() + "<br/> (" + moment(latestThing).format('MMMM Do YYYY, h:mm:ss a') + ")" + ((hoursSinceLastSpawn > 5 && hoursSinceLastDeath > 5) ? "<br/><i>The timer could likely be inaccurate, since server restarts etc. are not accounted for</i>" : "") + "");

        if (estimateData.latest.death <= 0 && estimateData.latest.spawn <= 0) {
            $("#lastTrackedWrapper").hide();
        }

        let duration = estimateData.estimate - now;
        let formattedTimer = moment.utc(duration).format("HH:mm:ss");
        if (duration > 0) {
            $("#time").text(formattedTimer);
            $("#timerText").text("The Magma Boss should spawn in about");
        } else {
            $("#time").text("NOW");
            $("#timerText").text("The Magma Boss should spawn");
        }
        $('head title', window.parent.document).text(formattedTimer + " | Hypixel Skyblock Magma Boss Timer");

        // Start timeout before showing any buttons, since it looks like a lot of people like clicking buttons with shiny colors
        if (startCounter > 15) {
            if (now % 2 === 0) {
                if (minutesUntilNextSpawn > 25) {
                    $("#waveBlazeBtn").hide();
                } else {
                    $("#waveBlazeBtn").show();
                }
                if (minutesSinceLastBlaze < 30 && minutesSinceLastBlaze > 5)
                    $("#waveBlazeBtn").attr("disabled", true);
                if (now - estimateData.latest.blaze < twentyMinsInMillis) {
                    $("#waveBlazeTime").text("(" + moment(estimateData.latest.blaze).fromNow() + ")");
                    $("#waveBlazeBtn").attr("data-tooltip", estimateData.latestConfirmations.blaze + " Confirmations");
                    // $("#waveBlazeBtn").attr("disabled", true);
                } else {
                    $("#waveBlazeTime").text("");
                    $("#waveBlazeBtn").attr("data-tooltip", "Not Confirmed");
                }

                if (minutesUntilNextSpawn > 15) {
                    $("#waveMagmaBtn").hide();
                } else {
                    $("#waveMagmaBtn").show();
                }
                if (minutesSinceLastMagma < 30 && minutesSinceLastMagma > 5)
                    $("#waveMagmaBtn").attr("disabled", true);
                if (now - estimateData.latest.magma < tenMinsInMillis) {
                    $("#waveMagmaTime").text("(" + moment(estimateData.latest.magma).fromNow() + ")");
                    $("#waveMagmaBtn").attr("data-tooltip", estimateData.latestConfirmations.magma + " Confirmations");
                    // $("#waveMagmaBtn").attr("disabled", true);
                } else {
                    $("#waveMagmaTime").text("");
                    $("#waveMagmaBtn").attr("data-tooltip", "Not Confirmed")
                }

                if (minutesUntilNextSpawn > 5) {
                    $("#musicBtn").hide();
                } else {
                    $("#musicBtn").show();
                }
                if (minutesSinceLastMusic < 30 && minutesSinceLastMusic > 5)
                    $("#musicBtn").attr("disabled", true);
                if (now - estimateData.latest.music < fiveMinsInMillis) {
                    $("#musicTime").text("(" + moment(estimateData.latest.music).fromNow() + ")");
                    $("#musicBtn").attr("data-tooltip", estimateData.latestConfirmations.music + " Confirmations");
                    // $("#musicBtn").attr("disabled", true);
                } else {
                    $("#musicTime").text("");
                    $("#musicBtn").attr("data-tooltip", "Not Confirmed");
                }

                if (minutesUntilNextSpawn > 4 && minutesSinceLastSpawn > 1) {
                    $("#spawnedBtn").hide();
                } else {
                    $("#spawnedBtn").show();
                }
                if (now - estimateData.latest.spawn < fiveMinsInMillis) {
                    $("#spawnTime").text("(" + moment(estimateData.latest.spawn).fromNow() + ")");
                    $("#spawnedBtn").attr("data-tooltip", estimateData.latestConfirmations.spawn + " Confirmations");
                    // $("#musicBtn").attr("disabled", true);
                } else {
                    $("#spawnTime").text("");
                    $("#spawnedBtn").attr("data-tooltip", "Not Confirmed")
                }

                if (minutesUntilNextSpawn > 1 && minutesSinceLastSpawn > 2) {
                    $("#deathBtn").hide();
                } else {
                    $("#deathBtn").show();
                }
                if (now - estimateData.latest.death < fiveMinsInMillis) {
                    $("#deathTime").text("(" + moment(estimateData.latest.death).fromNow() + ")");
                    $("#deathBtn").attr("data-tooltip", estimateData.latestConfirmations.death + " Confirmations");
                    // $("#musicBtn").attr("disabled", true);
                } else {
                    $("#deathTime").text("");
                    $("#deathBtn").attr("data-tooltip", "Not Confirmed")
                }

                // update tooltips
                $('.track-btn.tooltipped').tooltip({
                    position: "left"
                });

                if ($('.track-btn:visible').length) {
                    $("#buttonNote").show()
                } else {
                    $("#buttonNote").hide();
                }
            }
        }


        let message = "";
        if (duration < tenMinsInMillis) {
            message = "If you're not already in the Blazing Fortress, you should get going!";

            if (localStorage.getItem("tenMinNotification") === "true") {
                if (!tenMinuteNotification && !fiveMinuteNotification) {
                    tenMinuteNotification = showNotification("The Skyblock Magma Boss should spawn in less than 10 minutes!");
                }
            }
        } else {
            tenMinuteNotification = null;
        }
        if (duration < fiveMinsInMillis) {
            message = "Get ready!";

            if (localStorage.getItem("fiveMinNotification") === "true") {
                if (!fiveMinuteNotification) {
                    fiveMinuteNotification = showNotification("The Skyblock Magma Boss should spawn in less than five minutes!");
                }
            }
        } else {
            fiveMinuteNotification = null;
        }
        $("#suggestionMessage").text(message);
    }

    function refreshEstimate() {
        $.ajax("get_estimated_spawn" + (devMode ? "3" : "") + ".php").done(function (data) {
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
    setInterval(refreshEstimate, 30000);

    function ping() {
        $.ajax({
            method: "POST",
            url: "ping.php",
            data: {
                lastFocused: Math.floor(lastFocused),
                minecraftUser: $("#mcUsername").val(),
                ipv4: ipv4,
                ipv6: ipv6
            }
        })
    }

    ping();
    setInterval(ping, 30000);

    $("#waveBlazeBtn").click(function () {
        let $this = $(this);
        doEventPost($this, "blaze", "a blaze wave", false);
    });
    $("#waveMagmaBtn").click(function () {
        let $this = $(this);
        doEventPost($this, "magma", "a magma wave", false);
    });
    $("#musicBtn").click(function () {
        let $this = $(this);
        doEventPost($this, "music", "music", false);
    });
    $("#spawnedBtn").click(function () {
        let $this = $(this);
        doEventPost($this, "spawn", "a boss spawn", false);
    });
    $("#deathBtn").click(function () {
        let $this = $(this);
        doEventPost($this, "death", "a boss death", true);
    });

    function doEventPost($this, event, eventDescription, skipEstimateRefresh) {
        let username = $("#mcUsername").val();
        confirmAndCaptchaAdd(eventDescription, function (b) {
            if (b) {
                $this.attr("disabled", true);
                $.ajax({
                    method: "POST",
                    url: "add_event.php",
                    data: {
                        type: event,
                        captcha: reCaptchaToken,
                        username: username,
                        ipv4: ipv4,
                        ipv6: ipv6
                    }
                }).done(function () {
                    // $this.css("display", "none");
                    $this.attr("disabled", true);

                    if (!skipEstimateRefresh)
                        refreshEstimate();
                })
            }
        })
    }

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

    $("#fiveMinNotificationSwitch").prop("checked", localStorage.getItem("fiveMinNotification") === "true");
    $("#fiveMinNotificationSwitch").change(function () {
        let checked = $(this).is(":checked");
        if (checked) {
            Notification.requestPermission().then(function (result) {
                console.log(result);
                localStorage.setItem("fiveMinNotification", "true");
            });
        } else {
            localStorage.setItem("fiveMinNotification", "false");
        }
    });

    $("#mcUsername").val(localStorage.getItem("mcUsername") || "");
    $("#mcUsername").on("change", function () {
        localStorage.setItem("mcUsername", $(this).val());
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
                    formatter: function () {
                        let date = Highcharts.dateFormat("%y-%m-%d", this.x);
                        let time = Highcharts.dateFormat("%H:%M:%S", this.x);
                        return `<span style="color:${ this.point.color }">\u25CF</span> ${ this.point.name }<br/>` +
                            `<span>${ date } <b>${ time }</b></span><br/>` +
                            `<span>(${ this.point.confirmations } confirmations)</span>`
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

    $.getJSON("https://api.ipify.org?format=jsonp&callback=?", function (json) {
        ipv4 = json.ip;
        console.log("IPv4: " + ipv4);
    });
    $.getJSON("https://api6.ipify.org?format=jsonp&callback=?", function (json) {
        ipv6 = json.ip;
        console.log("IPv6: " + ipv6);
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