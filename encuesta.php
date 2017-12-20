<?php 

// Incluyo archivo para facilitar la comunicación con la base de datos empleando la metodología PDO. (Php data objects.)

include_once 'accesoDatos.php';

class Posicion
{
    public $latitud;
    public $longitud;

    public function Posicion($latitud, $longitud)
    {
        $this->latitud = $latitud;
        $this->longitud = $longitud;
    }
}

class Registro
{
    // Campos que posee un registro en la base de datos.
    public $id;
    public $encuesta;
    public $pregunta;
    public $respuesta;
    public $fecha;
    public $lat;
    public $lng;
}

class GestorInformes
{
    static public $arrayRespuestas;
    // Método que obtiene todas las respuestas y las almacena en un Arreglo.
    static function obtenerRespuestas($tipo)
    {
        $objetoAcceso = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAcceso->RetornarConsulta("SELECT * FROM encuestas WHERE encuesta = '".$tipo."'");
        $consulta->execute();
        // Almaceno el arreglo con los registros obtenidos.
        return $consulta->fetchAll(PDO::FETCH_CLASS,"Registro");
    }

    public static function traerCantidades($tipo)
    {
                // Traigo todos los registros del tipo de encuesta especificado.
                $arregloConTodasLasRespuestas = GestorInformes::obtenerRespuestas($tipo);
                // Obtengo cantidad de preguntas recorriendolas con un foreach
                $cantidadDePreguntas = 0;
                foreach($arregloConTodasLasRespuestas as $temp)
                {
                    if($temp->pregunta > $cantidadDePreguntas)
                    {
                        $cantidadDePreguntas = $temp->pregunta;
                    }
                }
                $arregloDeLaEncuesta = array();
                // Por cada pregunta realizamos un conteo en donde se verán:
                // En cada pregunta,busco la cantidad de respuestas, se las contará y se las agregará a un sub array (El cual luego será agregado al array de retorno)
                for($i = 0; $i < $cantidadDePreguntas; $i++)
                {
                    $arregloDeLaPregunta = array();
                    // Obtenemos cantidad de respuestas.
                    $respuestaMayor = 0;
                    // Estoy en la Pregunta i y voy a buscar la cantidad de respuestas (j)
                    foreach($arregloConTodasLasRespuestas as $temp)
                    {
                        $pregunta = $i + 1;
                        if($temp->pregunta == $pregunta)
                        {
                            // Si el numero de pregunta coincide.
                            if($temp->respuesta > $respuestaMayor)
                            {
                                $respuestaMayor = $temp->respuesta;
                            }
                        }
                    }
        
                    for($j = 0; $j < $respuestaMayor; $j++)
                    {
                        $conteoRespuesta = 0;
                        $respuesta = $j + 1;
                        foreach($arregloConTodasLasRespuestas as $temp)
                        {
                            if($temp->pregunta == $pregunta && $temp->respuesta == $respuesta)
                            {
                                $conteoRespuesta++;
                            }
                        }
                        $arregloDeLaPregunta[] = $conteoRespuesta;
                    }
                    $arregloDeLaEncuesta[] = $arregloDeLaPregunta;
                }
                return $arregloDeLaEncuesta;
    }

    public static function traerPorcentajes($tipo)
    {
        $arregloPrincipal = GestorInformes::traerCantidades($tipo);
        $arregloRetorno = array();
        // Recorro preguntas..
        foreach($arregloPrincipal as $temp)
        {
            $arregloPregunta = array();
            $cantidadRespondidaTotal = 0;
            // Recorro respuestas de las preguntas.
            foreach($temp as $respuesta)
            {
                $cantidadRespondidaTotal = $cantidadRespondidaTotal + $respuesta;
            }
            // Una vez obtenidos los totales puedo armar mi array de porcentajes.
            foreach($temp as $respuesta)
            {
                $porcentaje = $respuesta * 100 / $cantidadRespondidaTotal;
                $arregloPregunta[] = $porcentaje;
            }
            $arregloRetorno[] = $arregloPregunta;
        }
        return $arregloRetorno;
    }

     // +traer_data_respuestas(string encuesta, string tipo):array respuestas; (Ver formato si JSON o qué) :: Modo= Porcentaje o Totales.
    public static function traerRespuestas($tipo, $modo)
    {
        $retorno = null;
        if($modo == "cantidad")
        {
            $retorno = GestorInformes::traerCantidades($tipo);
        }else if($modo == "porcentaje")
        {
            $retorno = GestorInformes::traerPorcentajes($tipo);
        }
        return $retorno;
    }
}

class Mensaje
{
    public $cuerpo;
    public $objeto;

    public function Mensaje($cuerpo, $objeto)
    {
        $this->cuerpo = $cuerpo;
        $this->objeto = $objeto;
    }
}
class Encuesta
{
    // Identificamos el tipo de encuesta sea: '5'.'7a'.'7b'
    public $encuesta;
    // Cantidad preguntas
    public $cantidad;
    // Arreglo de preguntas.
    public $arrayPreguntas;
    // Latitud y longitud
    public $latitud;
    public $longitud;
    // Fecha
    public $fecha;

    // Tipo = Tipo de encuesta.  '5'.'7a'.'7b'
    // Arreglo = JSON String de el ARRAY de la encuesta [2,1,6]
    // Lat = Latitud : double
    // Lon = Longitud : double 

    public function Encuesta($encuesta, $arreglo, $latitud, $longitud)
    {
        // Establecemos la posición.
        $this->lat = $latitud;
        $this->lon = $longitud;
        // Establecemos el tipo de encuesta.
        $this->tipo = $encuesta;
        
        // Cargamos arrayPreguntas en función del arreglo obtenido.
        $aux = json_decode($arreglo);
        $i = 0;
        $this->arrayPreguntas = $aux;
        foreach ($aux as $temp) {
            $i++;
        }
        // Almacenamos cantidad de "preguntas que tenemos con respuesta" 
        $this->cantidad = $i;
        // La fecha actual
        $this->fecha = date("Y-m-d H:i:s");
    }

    // Métodos base de datos, empleados por los WebServices.

    /* 
    +guardar_datos(int encuesta, array respuestas, lat, lng):(mensaje, ultimoId);

    Array respuesta es una convención de como saber que pregunta es y que respuesta se eligió. --> [3,1,5]

    Pregunta 1 se eligió respuesta 3.
    Pregunta 2 se eligió respuesta 1.
    Pregunta 3 se eligió respuesta 5.
    */ 
    public function guardar_datos()
    {
        try
        {
            // Accedemos a las bibliotecas AccesoDatos.
            $datos = AccesoDatos::dameUnObjetoAcceso();
            $preguntaNumero = 1;
            foreach ($this->arrayPreguntas as $temp) {
                $consulta =$datos->RetornarConsulta("INSERT into encuestas (encuesta, pregunta, respuesta, fecha, lat, lng)".
                "values('$this->tipo','$preguntaNumero', '$temp' , '$this->fecha', '$this->lat', '$this->lon')");
                $consulta->execute();
                $preguntaNumero++;
            }
            // Retorno un JSON donde el primer elemento de un array es el mensaje y el segundo el ultimo id insertado.
            return ["Datos almacenados correctamente", $datos->RetornarUltimoIdInsertado()];
        }catch(Exception $e)
        {
            echo 'Excepción capturada - Error guardando datos de encuesta: ',  $e->getMessage(), "\n";
        }    
    }



}

?>