<?php
class Database {

    /** Recurso de Conexion */
    private $link;

    /** Parametros */
    private $host;
    private $usuario;
    private $clave;
    private $nombre;

    /** Mensaje de error */
    private $mensajeError = NULL;

    /** Constructor */
    public function __construct($host, $usuario, $clave, $nombre) {
        $this->host = $host;
        $this->usuario = $usuario;
        $this->clave = $clave;
        $this->nombre = $nombre;
    }
    /**
     * Se encarga de realizar la conexion a MySQL
     * Retorna TRUE si la conexion es exitosa, FALSE en caso contrario 
     */
    public function conectar() {

        $this->link = new mysqli($this->host, $this->usuario, $this->clave, $this->nombre);

        if ($this->link->connect_errno) {
            $this->__setMensajeError("No se pudo conectar al motor: " . $this->link->connect_error);
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Se encarga de realizar la consulta en MySQL
     * Retorna TRUE si la consulta es exitosa, 
     * Retorna ARRAY si la consulta arroja algun resultado
     * Retorna FALSE en caso contrario 
     */
    public function consulta($query) {

        if (($resultado = $this->link->query($query))) {

            if (is_object($resultado)) {
                return $this->__obtenerResultados($resultado);
            } else {
                return TRUE;
            }
        }

        $this->__setMensajeError("No se pudo obtener el resultado: " . $this->link->error);
        return FALSE;
    }

    /**
     * Se encarga de retornar el arreglo completo de resultados obtenidos
     * debido a que mysqli, no permite obtenerlos todos en un solo arreglo.
     * Liberando tambien la memoria asociada al resultado
     */
    private function __obtenerResultados(mysqli_result &$resultado) {

        $resultados = array();

        while ($fila = $resultado->fetch_assoc()) {
            $resultados[] = $fila;
        }

        $resultado->free();
        return $resultados;
    }

    /**
     * Retorna el ultimo identificador insertado en la base de datos
     */
    public function getLastID() {
        return $this->link->insert_id;
    }

    /**
     * Realiza el escapado de la cadena usando el motor
     */
    public function escape($cadena) {
        return $this->link->escape_string($cadena);
    }

    /**
     * Se encarga de realizar la desconexion de MySQL
     * Retorna TRUE si la desconexion es exitosa, FALSE en caso contrario 
     */
    public function desconectar() {
        if ($this->link && is_object($this->link)) {
            if ($this->link->close()) {
                return TRUE;
            } else {
                echo "Error al cerrar la conexión: " . $this->link->error;
                return FALSE;
            }
        }
        return FALSE;
    }
    

    /**
     * Guarda el mensaje de error
     */
    private function __setMensajeError($mensajeError) {
        $this->mensajeError = $mensajeError;
    }

    /**
     * Retorna el mensaje de error obtenido en la ultima operacion realizada en la base de datos
     */
    public function obtenerMensajeError() {
        return $this->mensajeError;
    }

    /** Destructor */
    public function Close() {
        $this->desconectar();
    }

}