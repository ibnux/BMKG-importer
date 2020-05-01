<?php
/**
 * Contoh dibuat oleh @ibnux
 */

$lat = -6.3730914;
$lon = 106.7116703;

$wilayah = json_decode(file_get_contents("https://ibnux.github.io/BMKG-importer/cuaca/wilayah.json"),true);
$jml = count($wilayah);

//  hitung jarak
for($n=0;$n<$jml;$n++){
    $wilayah[$n]['jarak'] = distance($lat, $lon, $wilayah[$n]['lat'], $wilayah[$n]['lon'], 'K');
}

//urutkan
usort($wilayah, 'urutkanJarak');

//ambil 5 besar aja
echo "<pre>";
echo "\n<h2>Urutkan dari yang terdekat<br>$lat,$lon</h2>\n";
echo "\n";
for($n=0;$n<5;$n++){
    print_r($wilayah[$n]);
    echo "\n";
}
echo "\n<h2>";
echo $wilayah[0]['propinsi'].",".$wilayah[0]['kota'].",".$wilayah[0]['kecamatan']."\n";
echo number_format($wilayah[0]['jarak'],2,",",".")." km</h2>\n";
echo "\n";

//ambil cuaca kota terdekat
$json = json_decode(file_get_contents("https://ibnux.github.io/BMKG-importer/cuaca/".$wilayah[0]['id'].".json"),true);
$time = time();
$n = 0;
echo '<table border="1"><tr>';
foreach($json as $cuaca){
    $timeCuaca = strtotime($cuaca['jamCuaca']);
    //yang lewat ngga perlu ditampilkan
    if($timeCuaca>$time){
        echo '<td>';
        echo '<img src="https://ibnux.github.io/BMKG-importer/icon/'.$cuaca['kodeCuaca'].'.png" class="image">';
        echo '<p>'.$cuaca['cuaca'].'</p>';
        echo "</td>\n";
    }
}
echo '</tr><table>';
echo "\n";

print_r($json);


function urutkanJarak($a, $b) {
    return $a['jarak'] - $b['jarak'];
}


// https://www.geodatasource.com/developers/php
function distance($lat1, $lon1, $lat2, $lon2, $unit) {
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    }
    else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}