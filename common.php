<?php

$FLOOR_LEVEL = 83;

$LAVA_STARTS = array(
    'A' => 94,
    'B' => 104,
    'C' => 99,
    'D' => 87,
    //TODO: add E
);

$LAVA_SPEED=array(// seconds for lava to advance one level (average) TODO
    'A'=>100,
    'B'=>100,
    'C'=>100,
    'D'=>100,
    'E'=>100
);

$BLAZE_SPAWNS = array(//TODO: figure out missing ones
    "B"=>91,
    "C" => 88
);

$MAGMA_SPAWNS = array(//TODO: figure out missing ones
    "B"=>86,
    "C" => 85
);

// Seems to take about 1:30 for the lava to advance 1 block (in any direction)
// Took 2:45 for one level (on y=86)
// 40 seconds after that, magma cubes spawned - advanced another block down around the same time
// ~ 3:20 for one y level
function estimateNextSpawn($lava_level, $lava_level_time, $last_spawn, $lowest_stream)
{
    global $LAVA_STARTS;
    global $FLOOR_LEVEL;
    global $BLAZE_SPAWNS;
    global $MAGMA_SPAWNS;

    $next_spawn = 0;
    if (isset($last_spawn)) {
//        $next_spawn = strtotime($lava_level_time . " +2hours");
        $next_spawn = "less than 2 hours";
    }
    if ($lava_level < $LAVA_STARTS[$lowest_stream]) {
        $next_spawn = "less than an hour";

        $diff = $lava_level - $FLOOR_LEVEL;
        $seconds = $diff * 120+60/* add about another minute to compensate for actual spawn delay */;
        $next_spawn = strtotime("+$seconds seconds");
    }
    if ($lava_level <= $FLOOR_LEVEL) {
//        $next_spawn = strtotime("+15minutes");
        if ($lowest_stream == "A" || $lowest_stream == "B" || $lowest_stream == "C") {
            $next_spawn = "1-5 minutes";
        }else {
            // D/E keep flowing into the ground level
            $next_spawn = "about 5-15 minutes";
        }
    }


    // Time between blaze and magma wave appears to be 10mins



    return $next_spawn;
}