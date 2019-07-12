<?php


//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once "common.php";

include "db_stuff.php";


$server = $_GET["server"];
if (!isset($server) || empty($server)) {
    die("missing server");
}

echo "$server |";

$id = 0;
$last_spawn = "";//TODO: should probably get the latest times from the separate table
$lava_level = 0;
$lava_level_time = "";
$spawn_times = array();
$events = array(
    "blaze" => [],
    "magma" => []
);
$lava_levels = array(
    "A" => [],
    "B" => [],
    "C" => [],
    "D" => [],
    "E" => []
);

$stmt = $conn->prepare("SELECT id,server,last_spawn,lava_level,lava_level_time,lowest_stream,last_event,last_event_time FROM hypixel_skyblock_magma_timer WHERE server=?");
$stmt->bind_param("s", $server);
$stmt->execute();
$stmt->bind_result($id, $server, $last_spawn, $lava_level, $lava_level_time, $lowest_stream, $last_event, $last_event_time);
$stmt->fetch();

if (isset($id)) {
    unset($stmt);

    $sId = 0;
    $rel = 0;
    $spawn_time = "";

    $stmt = $conn->prepare("SELECT id,rel,time FROM hypixel_skyblock_magma_timer_spawns WHERE rel=? ORDER BY time ASC");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->bind_result($sId, $rel, $spawn_time);
    while ($row = $stmt->fetch()) {
        $spawn_timestamp = strtotime($spawn_time);
        $spawn_times[] = $spawn_timestamp;
    }


    unset($stmt);


    $stmt = $conn->prepare("SELECT id,rel,time,type FROM hypixel_skyblock_magma_timer_events WHERE rel=? ORDER BY time ASC");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->bind_result($eId, $rel, $event_time, $eventType);
    while ($row = $stmt->fetch()) {
        $event_timestamp = strtotime($event_time);

        if (!isset($events[$eventType])) {
            $events[$eventType] = [];
        }

        $events[$eventType][] = $event_timestamp;

    }


    unset($stmt);


    $lId = 0;
    $rel = 0;
    $lava_time = "";
    $level = 0;

    $stmt = $conn->prepare("SELECT id,rel,level,time,stream FROM hypixel_skyblock_magma_timer_lava WHERE rel=? ORDER BY time ASC");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->bind_result($lId, $rel, $level, $lava_time, $stream);
    while ($row = $stmt->fetch()) {
        $lava_level_timestamp = strtotime($lava_time);

        if (!isset($lava_levels[$stream])) {
            $lava_levels[$stream] = [];
        }

        $lava_levels[$stream][] = array(
            $lava_level_timestamp * 1000,
            $level
        );
    }


    echo "<script>window.series = [";
    foreach ($lava_levels as $k => $v) {
        echo "{\"name\":\"$k\",";
        echo "\"data\":" . json_encode($v) . "},";
    }
    echo "];</script>";


    echo "<script>window.events = ";
    echo "{\"name\":\"Events\",";
    echo "\"type\":\"flags\",";
    echo "\"data\":[";
    foreach ($events as $k => $v) {
        foreach ($v as $s) {
            $s1 = $s * 1000;
            $name = "?";
            $text = "";
            if ($k == "blaze") {
                $name = "Blaze Wave";
                $text = "The Blaze Wave Spawned, Magma wave should be coming up ~10mins from here and spawn will happen in about 20mins";
            }
            if ($k == "magma") {
                $name = "Magma Cube Wave";
                $text = "The Blaze Wave Spawned, Spawn should happen in about 10mins from here";
            }
            if ($k == "music") {
                $name = "Music";
            }
            if ($k === "reboot") {
                $name = "Server Reboot";
                $text = "This server rebootet, which likely means the data here is no longer valid.";
            }
            echo "{\"x\":$s1,title:\"$name\",text:\"$text\"},";
        }
    }
    echo "]}";
    echo ";</script>";


    echo "<script>window.spawns = ";
    echo "{\"name\":\"Spawns\",";
    echo "\"type\":\"flags\",";
    echo "\"data\":[";
    foreach ($spawn_times as $s) {
        $s1 = $s * 1000;
        echo "{\"x\":$s1,title:\"Spawn\"},";
    }
    echo "]}";
    echo ";</script>";
} else {
    echo "Unknown Server";
}

$conn->close();


echo "<pre>";
echo "Server: $server\n\n";

echo "LastSpawn: $last_spawn\n";
echo "Spawns: \n";
var_dump($spawn_times);

echo "\nLastEvent: $last_event\n";
echo "$last_event_time\n";

echo "\nLavaLevel: $lava_level\n";
echo "$lava_level_time\n";
//echo "Levels: \n";
//var_dump($lava_levels);


echo "</pre>";

$lava_level_timestamp = strtotime($lava_level_time);
$last_spawn_timestamp = strtotime($last_spawn);
$last_event_timestamp = strtotime($last_event_time);


$estimatedSpawn = estimateNextSpawn($lava_level, $lava_level_timestamp, $last_spawn, $lowest_stream, $last_event, $last_event_timestamp);
echo "<h1>Estimated Spawn: <span " . (is_numeric($estimatedSpawn) ? ("data-time='$estimatedSpawn'") : ("")) . ">$estimatedSpawn</span></h1>";

echo '<div id="container"></div>';

echo "  <a href=\"list.php\">Show List</a>";


$conn->close();
?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
<!--<script src="https://code.highcharts.com/highcharts.js"></script>-->
<script src="https://code.highcharts.com/stock/highstock.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="timeFormatter.js"></script>
<script>
    $(document).ready(function () {

        window.series.push(window.spawns);
        window.series.push(window.events);

        console.log(window.series);


        Highcharts.chart('container', {
            chart: {
                type: 'spline'
            },
            title: {
                text: 'Timeline'
            },
            xAxis: {
                type: 'datetime',
                title: {
                    text: 'Time'
                }
            },
            yAxis: {
                title: {
                    text: 'Level'
                },
                min: 80
            },
            plotOptions: {
                spline: {
                    marker: {
                        enabled: true
                    }
                }
            },


            // Define the data points. All series have a dummy year
            // of 1970/71 in order to be compared on the same x axis. Note
            // that in JavaScript, months start at 0 for January, 1 for February etc.
            series: window.series
        });


        function refreshTimes() {
            $("span[data-time]").each(function () {
                let $this = $(this);
                formatTime($this);
            })
        }

        refreshTimes();
        setInterval(refreshTimes, 30000);


    })
</script>
