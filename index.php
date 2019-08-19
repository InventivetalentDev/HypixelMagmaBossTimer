<!DOCTYPE html>
<html>
    <head>
        <title>Hypixel Skyblock Magma Boss Timer</title>
        <link rel="icon" type="image/x-icon" href="favicon.ico"/>
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

            body, input {
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
                    <h5 class="center-align" id="timerText">The Magma Boss should spawn in about</h5>
                    <h1 class="center-align" id="time">00:00:00</h1>
                    <span id="nextTime"></span>
                    <br/>
                    <h4 class="center-align" id="suggestionMessage"></h4>
                    <br/>
                    <br/>
                    <span id="lastTrackedWrapper">Last tracked <span id="lastTrackedType">spawn</span> was <span id="lastTrackedTime"></span></span>
                </div>

                <br/>
                <br/>

                <div class="row center-align">
                    <div id="buttonNote" style="display:none;">
                        <strong>NOTE: Please click the buttons below <i>only</i> if the events actually occurred!</strong><br/>
                        <span>They will update the timer for <b>everyone</b>!</span><br/>
                    </div>
                    <button disabled class="btn tooltipped center-align track-btn amber" id="waveBlazeBtn" data-tooltip="Not confirmed" style="display: none;">
                        Blaze Wave Spawned <span id="waveBlazeTime"></span>
                    </button>
                    <br/>
                    <button disabled class="btn tooltipped center-align track-btn deep-orange" id="waveMagmaBtn" data-tooltip="Not confirmed" style="display: none;">
                        Magma Wave Spawned <span id="waveMagmaTime"></span>
                    </button>
                    <br/>
                    <button disabled class="btn tooltipped center-align track-btn purple darken-3" id="musicBtn" data-tooltip="Not confirmed" style="display: none;">
                        Mysterious Music Playing <span id="musicTime"></span>
                    </button>
                    <br/>
                    <button disabled class="btn tooltipped center-align track-btn red darken-4" id="spawnedBtn" data-tooltip="Not confirmed" style="display: none;">
                        Magma Boss Spawned <span id="spawnTime"></span>
                    </button>
                    <br/>
                    <button disabled class="btn tooltipped center-align track-btn  green darken-3" id="deathBtn" data-tooltip="Not confirmed" style="display: none;">
                        Magma Boss Died <span id="deathTime"></span>
                    </button>
                </div>
            </div>

        </div>

        <span style="position: fixed; bottom: 4px; left: 4px; color: gray;">Created by <a target="_blank" href="https://inventivetalent.org/?utm_source=hypixel_magma_tracker">inventivetalent</a> <span id="d"></span></span>

        <div id="captchaModal" class="modal">
            <div class="modal-content">

            </div>
        </div>

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
                            <div class="input-field col s12 m6">
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
                            <div class="input-field col s12 m6">
                                <span>5 Minute Notification</span>
                                <div class="switch">
                                    <label>
                                        Off
                                        <input type="checkbox" id="fiveMinNotificationSwitch">
                                        <span class="lever"></span>
                                        On
                                    </label>
                                </div>
                            </div>
                            <div class="input-field col s12 m6">
                                <input placeholder="username" id="mcUsername" type="text" class="validate" minlength="3" maxlength="16">
                                <label for="mcUsername">Minecraft Username</label>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="divider"></div>
                <br/>
                <div class="row">
                    <div class="col s6">
                        <span id="socialButtons">
                            <a href="https://twitter.com/Inventivtalent"><i class="fab fa-twitter"></i></a>
                            <a href="https://yeleha.co/discord"><i class="fab fa-discord"></i></a>
                            <a href="https://donation.inventivetalent.org/"><i class="fab fa-patreon"></i></a>
                            <!--<a href="https://github.com/InventivetalentDev/HypixelMagmaBossTimer"><i class="fab fa-github"></i></a>-->
                        </span>
                    </div>
                    <div class="col s6 right-align">
                        <span><a href="https://download.inventivetalent.org/gh/SkyblockBossTimerMod/1.2.0" target="_blank"><b>Download the Minecraft Mod</b> &nbsp; <i class="material-icons small" style="vertical-align:middle;">cloud_download</i></a></span>
                        <br/>
                        <span>(Automatically tracks events & shows time ingame)</span>
                    </div>
                </div>

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
            window.DOP_config = {
                links: { // Replace with your links
                    paypal: "https://paypal.me/inventivetalent",
                    patreon: "https://patreon.com/inventivetalent"
                },
                enableAnalytics: true
            }
        </script>
        <script src="https://cdn.jsdelivr.net/gh/InventivetalentDev/DonationPopup@master/DonationPopup.min.js"></script>

        <script src="script.min.js"></script>
    </body>
</html>