<?php
session_start();
include 'conn.php';
include 'interval.php';


$rows = array();


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="select count(a.ip) as count_danger
from (select ip , max(data) as data from demo_rfi.posizioni group by ip) a
join demo_rfi.posizioni b on a.ip = b.ip and a.data = b.data
JOIN demo_rfi.aree c on st_intersects(st_setsrid(st_makepoint(b.lon,b.lat),4326),c.geom)
where c.tipo ilike 'danger' 
and b.data > (SELECT current_timestamp at time zone 'UTC'- (".$interval." ||' minutes')::interval)
and b.data < (SELECT current_timestamp at time zone 'UTC'+ (10*".$interval." ||' minutes')::interval);"; // da metter 0.2 (2 decimi di minuto)
    //echo $query;
    $result = pg_query($conn, $query);

	
	while($r = pg_fetch_assoc($result)) {
		array_push($rows, $r);
	}
	
	$query="select count(a.ip) as count_safety
from (select ip , max(data) as data from demo_rfi.posizioni group by ip) a
join demo_rfi.posizioni b on a.ip = b.ip and a.data = b.data
JOIN demo_rfi.aree c on st_intersects(st_setsrid(st_makepoint(b.lon,b.lat),4326),c.geom)
where c.tipo ilike 'safety' 
and b.data > (SELECT current_timestamp at time zone 'UTC'- (".$interval." ||' minutes')::interval)
and b.data < (SELECT current_timestamp at time zone 'UTC'+ (10*".$interval." ||' minutes')::interval);"; // da metter 0.2 (2 decimi di minuto)
    
    $result = pg_query($conn, $query);

	
	while($r = pg_fetch_assoc($result)) {
    	array_push($rows, $r);
	}
	
	$query="select count(a.ip) as count_ip_connessi
from (select ip , max(data) as data from demo_rfi.posizioni group by ip) a
join demo_rfi.posizioni b on a.ip = b.ip and a.data = b.data
where b.data > (SELECT current_timestamp at time zone 'UTC'- (".$interval." ||' minutes')::interval)
and b.data < (SELECT current_timestamp at time zone 'UTC'+ (10*".$interval." ||' minutes')::interval);"; // da metter 0.2 (2 decimi di minuto)
    
    $result = pg_query($conn, $query);

	
	while($r = pg_fetch_assoc($result)) {
    	array_push($rows, $r);
	}
	
	
	pg_close($conn);
	
	

	
	//echo count($rows)."<br>";
	//echo $rows[0]."<br>";
	//print_r(array_values($rows));
	//echo $rows[1]['count_safety']."<br>";
	//echo $rows[2]['count_ip_connessi']."<br>";
	//echo $rows[0]['count_danger']."<br>";
	$result=$rows[2]['count_ip_connessi']-$rows[0]['count_danger']-$rows[1]['count_safety'];
	$check = array("count_fuori_aree"=>$result);
	//echo $result;
	array_push($rows, $check);
	
	if (empty($rows)==FALSE){
		//print $rows;
		print str_replace('},{',',',json_encode(array_values($rows)));
		//print str_replace('},{',',',json_encode(array_values(pg_fetch_all($result))));
	} else {
		echo "[{\"WARNING\":'In questo momento non ci sono dati disponibili'}]";
	}

    
}   
 ?>