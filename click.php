<?php
class Click
{
    public $id;
    public $pantalla;
    public $boton;
    public $lat;
    public $lng;
    public $fecha;

    /*
    Método guardar clicks: Se encarga de almacenar los clicks en la base de datos.
    */
    static public function dbGuardarClick($pantalla, $boton, $latitud, $longitud)
    {
        try
        {
            // Accedemos a las bibliotecas AccesoDatos.
            $datos = AccesoDatos::dameUnObjetoAcceso();
            $fecha = date("Y-m-d H:i:s");
            //id	fecha	lat	lng	pantalla boton
                $consulta = $datos->RetornarConsulta("INSERT into tablaclicks (fecha,lat,lng,pantalla,boton)".
                "values('$fecha','$latitud','$longitud','$pantalla','$boton')");
                $consulta->execute();
            return true;
        }catch(Exception $e)
        {
            echo 'Excepción capturada - Error guardando datos de Click: ',  $e->getMessage(), "\n";
        }    
    }

    /*
    Método traer clicks: Se encarga de traer clicks en la pantalla especificada en una distancia especificada con un $changui
    A partir de la latitud y la longitud establecida con un rango de fecha especificado. 
    */
    static public function traerClicks($pantalla, $latitud, $longitud, $changui, $fechaDesde, $fechaHasta)
    {
        try
        {
            $objetoAcceso = AccesoDatos::dameUnObjetoAcceso();
            echo "Filtrando desde " . $fechaDesde . " hasta " . $fechaHasta;
            $consulta = $objetoAcceso->RetornarConsulta("SELECT * FROM tablaclicks WHERE lat <= $latitud + $changui AND lat >= $latitud - $changui AND lng <= $longitud + $changui AND lng >= $longitud - $changui AND pantalla = $pantalla AND fecha >= '$fechaDesde' AND fecha <= '$fechaHasta'");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_CLASS,"Click");
        }catch(Exception $e)
        {
            echo 'Excepción capturada - Trayendo datos de los Clicks.: ',  $e->getMessage(), "\n";
        }   
    }
    /*
    Método traer todos los clicks: Trae todos los clicks de la base de datos.
    */
    static public function traerClicksTodos()
    {
        try
        {
            $objetoAcceso = AccesoDatos::dameUnObjetoAcceso();
            echo "Filtrando desde " . $fechaDesde . " hasta " . $fechaHasta;
            $consulta = $objetoAcceso->RetornarConsulta("SELECT * FROM tablaclicks");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_CLASS,"Click");
        }catch(Exception $e)
        {
            echo 'Excepción capturada - Trayendo datos de los Clicks.: ',  $e->getMessage(), "\n";
        }   
    }
}
?>