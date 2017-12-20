<?php

// Incluimos el framework Slim.
require_once 'vendor/autoload.php';
// Interfaces.
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once 'encuesta.php';
require_once 'click.php';

// Web services. 

$app = new \Slim\App;

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("");
    return $response;
});

//	id	encuesta pregunta	respuesta	fecha	lat	lng

$app->post('/guardar_datos', function (Request $request, Response $response) {
    // Recibimos los valores de la solicitud.
    // Indices esperados: 'encuesta', 'arreglo', 'latitud', 'longitud'
    $encuesta = $request->getParsedBody()["encuesta"];
    $arreglo = $request->getParsedBody()["arreglo"];
    $latitud = $request->getParsedBody()["latitud"];
    $longitud = $request->getParsedBody()["longitud"];
    $objetoEncuesta = new Encuesta($encuesta, $arreglo, $latitud, $longitud);
    $response->getBody()->write(json_encode($objetoEncuesta->guardar_datos()));
    return $response;
});

$app->post('/traer_data_respuestas', function (Request $request, Response $response) {
    $encuesta = $request->getParsedBody()["encuesta"];
    $modo = $request->getParsedBody()["modo"];
    $estadisticas = GestorInformes::traerRespuestas($encuesta, $modo);
    $response->getBody()->write(json_encode($estadisticas));
    return $response;
});

$app->get('/traer_data_respuestas', function (Request $request, Response $response) {
    $estadisticas = GestorInformes::traerRespuestas("7a", "cantidad");
    $response->getBody()->write(json_encode($estadisticas));
    return $response;
});

	## guardar_click
   // data:{fecha, pantalla, lat, lng, boton}
   // retorna: 1 || 0
$app->post('/guardar_click', function(Request $request, Response $response){
    try
    {
        $pantalla = $request->getParsedBody()["pantalla"];
        $boton = $request->getParsedBody()["boton"];
        $latitud = $request->getParsedBody()["latitud"];
        $longitud = $request->getParsedBody()["longitud"];
        //Click($pantalla, $boton, $latitud, $longitud)
        $temp = new Click($pantalla, $boton, $latitud, $longitud);
        return $temp->dbGuardarClick(); 
    }catch(Exception $e)
    {
        echo 'Excepción capturada - Error almacenando Click: ',  $e->getMessage(), "\n";
    }
});
/*
## traer_clicks
		data: {pantalla, entre_cual_fecha, hasta_que_fecha, lat, lng, changuiLatLng} 
		devuelve: [{id,pantalla,fecha,lat,lng,boton},etc]
*/
$app->post('/traer_clicks', function(Request $request, Response $response){
    $pantalla = $request->getParsedBody()["pantalla"];
    $desde = $request->getParsedBody()["desde"];
    $hasta = $request->getParsedBody()["hasta"];
    $latitud = $request->getParsedBody()["latitud"];
    $longitud = $request->getParsedBody()["longitud"];
    $changui = $request->getParsedBody()["changui"];
    // Espera la fecha con el siguiente formato 2017-12-20 02:14:39
    //entonces le mandarias a la api: una ubicacion y un changui (select * from tabla where lat>ubiicacion-changui and lat<ubicacion+changui...)    
});

$app->run();

?>