<?php
class Click
{
    public $pantalla;
    public $boton;
    public $latitud;
    public $longitud;
    public $fecha;

    public function Click($pantalla, $boton, $latitud, $longitud)
    {
        $this->fecha = date("Y-m-d H:i:s");
        $this->pantalla = $pantalla;
        $this->boton = $boton;
        $this->latitud = $latitud;
        $this->longitud = $longitud;
    }

    public function dbGuardarClick()
    {
        try
        {
            // Accedemos a las bibliotecas AccesoDatos.
            $datos = AccesoDatos::dameUnObjetoAcceso();
            //id	fecha	lat	lng	pantalla boton
                $consulta =$datos->RetornarConsulta("INSERT into tablaclicks (fecha,lat,lng,pantalla,boton)".
                "values('$this->fecha','$this->latitud','$this->longitud','$this->pantalla','$this->boton')");
                $consulta->execute();
            return true;
        }catch(Exception $e)
        {
            echo 'Excepción capturada - Error guardando datos de Click: ',  $e->getMessage(), "\n";
        }    
    }
}
?>