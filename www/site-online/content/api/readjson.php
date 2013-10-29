<?php
//read json file for each server and reduce qty in database
//somehow remember the quantity to be sent
function readJson()
{
$file = 'receive/S01.json';
$json = json_decode(file_get_contents($file), true);
echo $json['products'][0]['barcode'];
}

readJson();

?>