<?php


//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once "common.php";

include "db_stuff.php";


function getServerId($conn, $server)
{
    $id = -1;

    $stmt = $conn->prepare("SELECT id,server FROM hypixel_skyblock_magma_timer WHERE server=?");
    $stmt->bind_param("s", $server);
    $stmt->execute();
    $stmt->bind_result($id, $server);
    $stmt->fetch();

    return $id;
}


$add = $_POST["add"];
if (isset($add)) {
    $type = $add;

    $server = $_POST["server"];
    if (!isset($server) || empty($server)) {
        die("missing server");
    }

    echo "<pre>";


    $serverId = getServerId($conn, $server);
    unset($stmt);

    echo "Server: $server (#$serverId)\n";

    if ($serverId == -1) {
        // Add new server
        $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer (server) VALUES(?)");
        $stmt->bind_param("s", $server);
        $stmt->execute();

        $serverId = getServerId($conn, $server);
    }

    if ($serverId == -1) {
        die("unexpected state: serverId is invalid after insertion");
    }


    $date = date("Y-m-d H:i:s");
    if ($type === "lava") {
//        $l = $_POST["level"];
//        $s =  $_POST["stream"];
//        if (!isset($l)) {
//            die("missing lava level");
//        }
//        if (!isset($s)) {
//            $s = null;
//        }

        $streams = $_POST["streams"];

        $lowestPercent = 100;
        $lowest = 100;
        $lowestName = "";

        $percents = [];

        foreach ($streams as $k => $v) {
            $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_lava (rel,level,stream) VALUES(?,?,?)");
            $stmt->bind_param("iis", $serverId, $v, $k);
            $stmt->execute();

            unset($stmt);

            if ($v == 0||is_nan($v)) {
                continue;
            }

            $percent = $v / $LAVA_STARTS[$k];

            if ($percent < $lowestPercent) {
                $lowestPercent = $percent;
                $lowest = $v;
                $lowestName = $k;
            }

            $percents[$k]=$percent;
        }

        $stmt = $conn->prepare("UPDATE hypixel_skyblock_magma_timer SET lava_level=?,lava_level_time=?,lowest_stream=? WHERE id=?");
        $stmt->bind_param("issi", $lowest, $date, $lowestName, $serverId);
        $stmt->execute();

        unset($stmt);


        echo "Lava levels added\n";
        print_r($percents);


    } else if ($type === "spawn") {

        $stmt = $conn->prepare("UPDATE hypixel_skyblock_magma_timer SET last_spawn=? WHERE id=?");
        $stmt->bind_param("si", $date, $serverId);
        $stmt->execute();

        unset($stmt);


        $stmt = $conn->prepare("INSERT INTO hypixel_skyblock_magma_timer_spawns (rel,time) VALUES(?,?)");
        $stmt->bind_param("is", $serverId,$date);
        $stmt->execute();

        echo "Spawn added";
    } else {
        die("unsupported add");
    }

    $conn->close();

    echo "</pre>";
    echo "<hr/>";
}


echo "<br/>This thing is meant to help everyone, so please don't abuse it, thanks! :)<br/>";

echo '
    <form action="add.php" method="post">
      <h2>Add Lava Level</h2>
      
      <label>
      <input type="text" name="server" placeholder="miniXXY">
      Server
      </label>
      <input type="hidden" name="add" value="lava"><br/>
      <br/>
      
      There should be exactly ONE stream which is lower than the prefilled numbers below.<br/>
      See <a href="https://hypixel.net/threads/guide-magma-boss-indicators.2154296/">hypixel.net/threads/guide-magma-boss-indicators.2154296</a> for more.
      <br/>
      
       <!--
      <label>
      <input type="number" name="level" placeholder="69">
      Lava Level (Y-Coordinate) of the lowest lava river (only one will flow). 
      </label> <br/>
    
    
    <label>
        <select name="stream">
        <option selected></option>
        <option value="A">A (&lt;94)</option>
        <option value="B">B (&lt;104)</option>
        <option value="C">C (&lt;99)</option>
        <option value="D">D</option>
        <option value="E">E</option>
</select>
Which stream is the lowest?
</label><br/>
-->

<label><input type="number" name="streams[A]" value="94">A</label><br/>
<label><input type="number" name="streams[B]" value="104">B</label><br/>
<label><input type="number" name="streams[C]" value="99">C</label><br/>

<label><input type="number" name="streams[D]" value="87">D</label><br/>
<label><input type="number" name="streams[E]" value="">E</label><br/>
    
    <img src="https://yeleha.co/2XYXRe3">  
    
      
      <br/>
      <br/>
      <button type="submit">Add it!</button>
    </form>
    
    <hr/>
    
    <form action="add.php" method="post">
      <h2>Add Spawn</h2>
      
      <label>
      <input type="text" name="server" placeholder="miniXXY">
      Server
      </label>
      <input type="hidden" name="add" value="spawn">
      
      <br/>
      <br/>
      <button type="submit">Add it!</button>
    </form>
    
    <br/>
    
    <a href="list.php">Show List</a>
    ';
