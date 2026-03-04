<?php
use Symfony\Component\HttpFoundation\JsonResponse;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$dir_assets = "";
$padre = dirname(__DIR__);

$folderPath = $padre . "/upload/";
$postdata = file_get_contents("php://input");
if (!empty($postdata)) {
    $request = json_decode($postdata);
    $idPersona = $request->idPersona;

    $dir_assets = $padre . "\assets";
    $dir = $dir_assets . "\imgs\persona";
    $micarpeta = $dir . "/" . $idPersona;
    $folderPath = $micarpeta . "/foto/";
    // si no existe la carpeta con el idPersona se crea
    if (!file_exists($micarpeta)) {
        //crea el directorio
        mkdir($micarpeta, 0777, true);
        //crea sub-directorio foto y documento
        $dir_foto = $micarpeta . "/foto";
        mkdir($dir_foto, 0777, true);
        mkdir($micarpeta . "\documento", 0777, true);
    }

    // GUARDO LA IMAGEN EN SERVIDOR
    $image_parts = explode(";base64,", $request->image);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_base64 = base64_decode($image_parts[1]);
    $file = $folderPath . uniqid() . '.png';
    if (file_put_contents($file, $image_base64)) {
        $response[] = array('sts' => true, 'msg' => 'Successfully uploaded');
    }
    echo json_encode($response);
}

?>