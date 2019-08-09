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

            body{
                background-color: rgb(55, 40, 47);
                color: #e9e9e9;
            }

            #bgImage{
                height: 100%;
                filter: blur(4px);
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
            }

            #content{
                position: absolute;

                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);

                z-index: 2;
            }


            #time {
                font-size: 4rem;
            }
        </style>
    </head>
    <body>
        <div id="bgImage"></div>

        <div id="content" class="container">

            <div class="center-align">
                <div class="row center-align">
                    <h5 class="center-align">The Magma Boss should spawn in about</h5>
                    <h1 class="center-align" id="time">00:00:00</h1>
                </div>

                <br/>
                <br/>
                <br/>
                <br/>

                <div class="row center-align">
                    <button class="btn center-align" id="spawnedBtn">
                        Please click me when it spawned!
                    </button>
                </div>
            </div>

        </div>


        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        <script>
            $(document).ready(function () {
                let bgIndex = getRndInteger(1, 8);
                $("#bgImage").css("background-image", "url(img/bg/" + bgIndex + ".jpg), url(img/bg/"+bgIndex+".png)");

                $.ajax("get_estimated_spawn.php").done(function (data) {
                    console.log(data);


                });

                $("#spawnedBtn").click(function () {
                    $(this).attr("disabled", true);
                    $.ajax({
                        method:"POST",
                        url:"add_spawn.php",
                        data:{}
                    }).done(function () {
                        alert("Spawn added. Thanks! :)")
                    })
                })

                function getRndInteger(min, max) {
                    return Math.floor(Math.random() * (max - min) ) + min;
                }
            })
        </script>
    </body>
</html>