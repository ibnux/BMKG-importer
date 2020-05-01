<?php
/**
 * Script ini untuk import data dari BMKG ke database internal anda
 * Sehingga jika dibutuhkan, data cuaca tidak perlu tembak lagi ke BMKG
 * dari API nya sendiri, BMKG menyediakan sampai 3 hari ke depan
 * Jalankan Script ini dari Command Line, gunakan Crontab agar di eksekusi tiap hari
 * contoh crontab https://crontab.guru/#0_3_*_*_*
 *
 * Dibuat oleh Ibnu Maksum @ibnux
 * Sumber BMKG: http://data.bmkg.go.id/
 * */
include "config.php";

// Propinsi yang tersedia di situs BMKG
$props = array('Aceh','Bali','BangkaBelitung','Banten','Bengkulu','DIYogyakarta','DKIJakarta','Gorontalo','Jambi','JawaBarat',
            'JawaTengah','JawaTimur','KalimantanBarat','KalimantanSelatan','KalimantanTengah','KalimantanTimur',
            'KalimantanUtara','KepulauanRiau','Lampung','Maluku','MalukuUtara','NusaTenggaraBarat','NusaTenggaraTimur',
            'Papua','PapuaBarat','Riau','SulawesiBarat','SulawesiSelatan','SulawesiTengah','SulawesiTenggara','SulawesiUtara',
            'SumateraBarat','SumateraSelatan','SumateraUtara');

//Kode Cuaca untuk ambil ikonnya
$kodeCuaca = array(
    0 => "Cerah",
    1 => "Cerah Berawan",
    2 => "Cerah Berawan",
    3 => "Berawan",
    4 => "Berawan Tebal",
    5 => "Udara Kabur",
    100 => "Cerah",
    101 => "Cerah Berawan",
    102 => "Cerah Berawan",
    103 => "Berawan",
    104 => "Berawan Tebal",
    10 => "Asap",
    45 => "Berkabut",
    60 => "Hujan Ringan",
    61 => "Hujan Sedang",
    63 => "Hujan Lebat",
    80 => "Hujan Lokal",
    95 => "Hujan Petir",
    97 => "Hujan Petir"
);

//Mulai Looping tiap propinsi
foreach($props as $prop){
    //Jika script tidak jalan, mungkin harus ganti url ini
    $xml_string = file_get_contents("http://data.bmkg.go.id/datamkg/MEWS/DigitalForecast/DigitalForecast-".$prop.".xml");
    $xml = simplexml_load_string($xml_string);
    //Ubah array ke Json Structure String
    $json = json_encode($xml);
    //hapus @
    $json = str_replace('"@attributes"','"attributes"',$json);
    //Kembalikan menjadi Json, kayanya kerja 2x yah :D
    $array = json_decode($json,TRUE);

    //Masuk ke objek area
    $areas = $array['forecast']['area'];
    if(count($areas)>0){
        foreach($areas as $area){
            $idWilayah = $area['attributes']['id'];
            //cek apakah sudah ada di database
            if(!$db->has("t_wilayah",['id'=>$idWilayah])){
                //Tambahkan ke tabel
                $db->insert("t_wilayah",[
                    'id'=>$idWilayah,
                    'propinsi'=>$prop,
                    'kota'=>$area['name'][1],
                    'kecamatan'=>$area['name'][0],
                    'lat'=>$area['attributes']['latitude'],
                    'lon'=>$area['attributes']['longitude']]
                );
                //Cek lagi apakah sukses atau ngga, jika sukses tentu sudah ada di database
                if($db->has("t_wilayah",['id'=>$idWilayah])){
                    echo $idWilayah." ".$area['attributes']['domain']." ".$area['attributes']['description']." ADDED\n";
                }else{
                    echo $idWilayah." ".$area['attributes']['domain']." ".$area['attributes']['description']." FAILED\n";
                }
            }else{
                echo $idWilayah." ".$area['attributes']['domain']." ".$area['attributes']['description']." EXISTS\n";
            }

            //parsing ramalan cuaca, data lain tidak diambil seperti arah angin
            $params = $area['parameter'];
            if(is_array($params) && count($params)>0){
                foreach($params as $param){
                    //Jika data cuaca
                    if($param['attributes']['id']=='weather'){
                        //Tambahkan ke database
                        $times = $param['timerange'];
                        foreach($times as $tm){
                            $jam = $tm['attributes']['datetime'];
                            $y = substr($jam,0,4);
                            $m = substr($jam,4,2);
                            $d = substr($jam,6,2);
                            $h = substr($jam,8,2);
                            $i = substr($jam,10,2);
                            if(!$db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00"]])){
                                $db->insert("t_cuaca",
                                    ['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00",'kodeCuaca'=>$tm['value'],'cuaca'=>$kodeCuaca[$tm['value']]]);
                                if($db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00"]])){
                                    echo "jamCuaca $y-$m-$d $h:$i:00 kodeCuaca ".$tm['value']." INSERT\n";
                                }else{
                                    echo "jamCuaca $y-$m-$d $h:$i:00 kodeCuaca ".$tm['value']." FAILED\n";
                                }
                            }else{
                                if(!$db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00",'kodeCuaca'=>$tm['value']]])){
                                    //ada perbedaan, update dong
                                    $db->update("t_cuaca",
                                        ['kodeCuaca'=>$tm['value'],'cuaca'=>$kodeCuaca[$tm['value']]],
                                        ['AND'=>['idWilayah'=>$idWilayah, 'jamCuaca'=>"$y-$m-$d $h:$i:00"]]);

                                    echo "jamCuaca $y-$m-$d $h:$i:00 kodeCuaca ".$tm['value']." UPDATE\n";
                                }else{
                                    // isinya sama
                                    echo "jamCuaca $y-$m-$d $h:$i:00 kodeCuaca ".$tm['value']." EXISTS\n";
                                }
                            }
                        }

                    //Jika data kelembaban
                    }else if($param['attributes']['id']=='hu'){
                        //Tambahkan ke database
                        $times = $param['timerange'];
                        foreach($times as $tm){
                            $jam = $tm['attributes']['datetime'];
                            $y = substr($jam,0,4);
                            $m = substr($jam,4,2);
                            $d = substr($jam,6,2);
                            $h = substr($jam,8,2);
                            $i = substr($jam,10,2);
                            if(!$db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00"]])){
                                $db->insert("t_cuaca",['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00",'humidity'=>$tm['value']]);
                                if($db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00"]])){
                                    echo "humidity $y-$m-$d $h:$i:00 humidity ".$tm['value']." INSERT\n";
                                }else{
                                    echo "humidity $y-$m-$d $h:$i:00 humidity ".$tm['value']." FAILED\n";
                                }
                            }else{
                                if(!$db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00",'humidity'=>$tm['value']]])){
                                    //ada perbedaan, update dong
                                    $db->update("t_cuaca",
                                        ['humidity'=>$tm['value']],
                                        ['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00"]]);
                                    echo "jamCuaca $y-$m-$d $h:$i:00 kodeCuaca ".$tm['value']." UPDATE\n";
                                }else{
                                    // isinya sama
                                    echo "jamCuaca $y-$m-$d $h:$i:00 kodeCuaca ".$tm['value']." EXISTS\n";
                                }
                            }
                        }
                    //Jika data Temperatur
                    }else if($param['attributes']['id']=='t'){
                        //Tambahkan ke database
                        $times = $param['timerange'];
                        foreach($times as $tm){
                            $jam = $tm['attributes']['datetime'];
                            $y = substr($jam,0,4);
                            $m = substr($jam,4,2);
                            $d = substr($jam,6,2);
                            $h = substr($jam,8,2);
                            $i = substr($jam,10,2);
                            if(!$db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00"]])){
                                $db->insert("t_cuaca",['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00",'tempC'=>$tm['value'][0],'tempF'=>$tm['value'][1]]);
                                if($db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00"]])){
                                    echo "humidity $y-$m-$d $h:$i:00 humidity ".$tm['value']." INSERT\n";
                                }else{
                                    echo "humidity $y-$m-$d $h:$i:00 humidity ".$tm['value']." FAILED\n";
                                }
                            }else{
                                if(!$db->has("t_cuaca",['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00",'tempC'=>$tm['value'][0],'tempF'=>$tm['value'][1]]])){
                                    //ada perbedaan, update dong
                                    $db->update("t_cuaca",
                                        ['tempC'=>$tm['value'][0],'tempF'=>$tm['value'][1]],
                                        ['AND'=>['idWilayah'=>$idWilayah,'jamCuaca'=>"$y-$m-$d $h:$i:00"]]);
                                    echo "jamCuaca $y-$m-$d $h:$i:00 temp ".$tm['value'][0]."-".$tm['value'][1]." UPDATE\n";
                                }else{
                                    // isinya sama
                                    echo "jamCuaca $y-$m-$d $h:$i:00 temp ".$tm['value'][0]."-".$tm['value'][1]." EXISTS\n";
                                }
                            }
                        }
                    }
                }//foreach($params as $param){
            }//if(count($params)>0){
        }//foreach($areas as $area){
    }//if(count($areas)>0){
}

// GENERATE FILE UNTUK HARI INI,
// HAPUS JIKA TIDAK BUTUH

$wilayah = $db->select("t_wilayah","*",["ORDER"=>"propinsi ASC"]);

//simpan ke file
file_put_contents("./cuaca/wilayah.json",json_encode($wilayah));

foreach($wilayah as $wil){
    $id = $wil['id'];
    $cuaca = $db->query('SELECT jamCuaca,kodeCuaca,cuaca,humidity,tempC,tempF FROM t_cuaca WHERE DATE(jamCuaca)>=DATE(NOW()) AND idWilayah='.$id.' ORDER BY jamCuaca ASC')->fetchAll(PDO::FETCH_ASSOC);

    //simpan ke file
    file_put_contents("./cuaca/$id.json",json_encode($cuaca));
}

//KIRIM KE GIT
//HAPUS jika tidak dibutuhkan
echo shell_exec('git add . && git commit -m "Update Cuaca tanggal '.date("d M Y H:i").' " && git push');
