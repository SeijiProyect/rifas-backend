<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once('config.php');

$conexion = create_connection();
if (!$conexion) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

log_addcron("---- INICIO ----");

$fecha_actual_aux = date("Y-m-d H:i:s");
log_addcron("fecha_actual_aux " . $fecha_actual_aux);
$fecha_actual_aux = strtotime($fecha_actual_aux);
$fecha_actual = date("Y-m-d H:i:s", $fecha_actual_aux + 60);

$sql = "SELECT * FROM notificacion WHERE fecha_programada <= '" . $fecha_actual . "' AND fecha_enviado IS NULL";
$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al ejecutar la consulta: " . $conexion->error);
}

$notificaciones = array();
while ($fila = $resultado->fetch_assoc()) {
    $notificaciones[] = $fila;
}

log_addcron("foreach notificaciones");
foreach ($notificaciones as $notificacion) {
    if ($notificacion['fecha_enviado'] == null) {
        $sql_query = '';
        if ($notificacion['ciudad_id'] !== NULL) {
            $sql_query = "SELECT ptf.token, p.id FROM itinerario AS i, itinerario_detalle AS itd, pasajero AS p, persona AS pe, persona_token_firebase AS ptf
            WHERE itd.ciudad_id = " . $notificacion['ciudad_id'] . " AND itd.itinerario_id = i.id AND i.id = p.itinerario_id AND
            p.persona_id = pe.id AND pe.id = ptf.persona_id";
        } else if ($notificacion['lista_id'] !== NULL) {
            $sql_query = "SELECT ptf.token, p.id FROM lista AS l, lista_opcion AS lo, pasajero_lista_opcion AS plo, pasajero AS p, 
            persona AS pe, persona_token_firebase AS ptf WHERE l.id = " . $notificacion['lista_id'] . " AND lo.lista_id = l.id AND plo.lista_opcion_id = lo.id
            AND plo.pasajero_id = p.id AND p.persona_id = pe.id AND pe.id = ptf.persona_id";
        } else if ($notificacion['itinerario_id'] != NULL) {
            $sql_query = "SELECT ptf.token, p.id FROM pasajero AS p, persona AS pe, persona_token_firebase AS ptf
            WHERE p.itinerario_id = " . $notificacion['itinerario_id'] . " AND p.persona_id = pe.id AND pe.id = ptf.persona_id";
        } else {
            $sql_query = "SELECT ptf.token, p.id FROM itinerario AS i, pasajero AS p, persona AS pe, persona_token_firebase AS ptf
            WHERE i.grupo_id = " . $notificacion['grupo_id'] . " AND i.id = p.itinerario_id AND p.persona_id = pe.id AND pe.id = ptf.persona_id";
        }
        $sql_query_result = $conexion->query($sql_query);
        $usuarios = array();
        while ($fila = $sql_query_result->fetch_assoc()) {
            $usuarios[] = $fila;
        }
        log_addcron("foreach usuarios");
        foreach ($usuarios as $usuario) {
            if (isset($usuario['token']) && $usuario['token'] !== null) {
                $response_send_notification = send_notification($notificacion['titulo'], $notificacion['mensaje'], $usuario['token'],  $notificacion['id'], $notificacion['foto']);
                if ($response_send_notification['success'] !== null && $response_send_notification['success'] == 1) {
                    $notification_id = $notificacion['id'];
                    $pasajero_id = $usuario['id'];
                    $sql = "INSERT INTO pasajero_notificacion (pasajero_id, notificacion_id, fecha_visto) VALUES ($pasajero_id, $notification_id, null)";
                    $conexion->query($sql);
                }
            }
        }
        $notification_id = $notificacion['id'];
        $sql = "UPDATE notificacion SET fecha_enviado='$fecha_actual' WHERE id=$notification_id";
        $conexion->query($sql);
    }
}

log_addcron("---- FIN ----\n\n");

function send_notification($title_notification, $text_notification, $token_usr, $id_notification, $image_notification)
{
    log_addcron("send_notification");
    $imagen_ruta = 'http://apirifas.detoqueytoque.com/' . $image_notification;
    //ENVIA LA NOTIFICACION
    $url = 'https://fcm.googleapis.com/fcm/send';
    $api_key = 'AAAA4-DndCw:APA91bGPkhl-8nlMYPRChXK7P2S973_jBpp2tzhbEEcPLRIuLgYplrZ7ccZJHIR6i2LlQCdTl4Pkn4DSUpLIIRwVmXkAKbugXfOfrEGxsLWDPeg0f-LJw3hzD_zbQ5HIkghBZElSKrFn';
    $fields = array(
        // 'registration_ids' => array (
        //   $token
        // ),
        "notification" => array(
            "title" => $title_notification,
            "body" => $text_notification,
            "badge" => 1,
            "image" => $imagen_ruta,
            "sound" => array(
                "critical" => 1,
                "name" => "default",
                "volume" => 1.0
            ),
        ),
        "android" => array(
            "ttl" => "3600s",
            "priority" => "high",
            "color" => "#00d592",
        ),
        "apns" => array(
            "headers" => array(
                "apns-priority" => "5",
            ),
            "payload" => array(
                "aps" => array(
                    "badge" => 1,
                ),
                "mutable_content" => true,
                "content_available" => true,
            ),
        ),
        "data" => array(
            "body" => 'https://www.google.com',
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
            "status" => "done",
            "notification_id" => $id_notification,
        ),
        "to" => $token_usr,
        //
        'priority' => 'high'
    );

    //header includes Content type and api key
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . $api_key
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);

    if ($result === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    $array_result = json_decode($result, true);

    return $array_result;
}

function log_addcron($new_data)
{
    $new_data = date("Ymd G:i:s") . "  >>  " . $new_data;
    $my_file = dirname(__FILE__) . '/logs/' . 'log_addcron' . date("Y-m-d") . '.log';
    $handle = fopen($my_file, 'a') or die('Cannot open file:  ' . $my_file);
    fwrite($handle, $new_data . "\n");
}
