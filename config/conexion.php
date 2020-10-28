<?php
date_default_timezone_set('America/Bogota');
setLocale(LC_ALL, "es_CO");

class Conexion {
    private $datos;
    public $conexion;

    public function __construct() {
        include __DIR__."/datos.php";

        $this->datos = $CONEXION["desarrollo"];
    }

    public function Conectar() {
        $str_con = "mysql:host=". $this->datos["host"];
        
        if($this->datos["port"] > 0) {
            $str_con .= ":". $this->datos["port"];
        }

        $str_con .= ";dbname=". $this->datos["db"];

        try {
            $this->con = new PDO($str_con, $this->datos["user"], $this->datos["pass"]);

        } catch(Exception $e) {
            echo "<b>Hubo un error al conectar</b><br>";
        }       
    }

    public function Desconectar() {
        $this->conexion = null;
    }

    /**
     * @param $parametros
     * Recibe un arreglo
     * [campo, comparador, llave, valor]
     * [campo, llave, valor]
     *
     * Ejemplo
     * ["u.nombres", "LIKE", ":nombre", "%algo%"]
     * ["u.id", ":id", 1]
     * [":id", 1]
    */
    public function construirParametros($parametros) {
        try {
            # Parametrizacion de Consulta
            $campos_where = [];
            $params_sent = [];

            foreach($parametros as $arreglo) {
                switch( count($arreglo) ) {
                    case 4:
                    array_push(
                        $campos_where, 
                        "$arreglo[0] $arreglo[1] $arreglo[2]"
                    );
                    break;

                    case 3:
                    array_push(
                        $campos_where, 
                        "$arreglo[0] = $arreglo[1]"
                    );
                    break;

                    case 2:
                    $c = str_replace(":", "", $arreglo[0]);
                    array_push(
                        $campos_where, 
                        "areas.id = :$c"
                    );
                    break;
                }

                $key_value = array_slice($arreglo, -2, 2);
                $params_sent[$key_value[0]] = $key_value[1];
            }

            return [
                "sql_params" => $campos_where,
                "pdo_params" => $params_sent
            ];
        } catch(Exception $e) {
            echo "Error:<pre>";
            print_r($e);
            echo "</pre>";
        }
    }

    public function ejecutar($sql) {
        self::Conectar();

        $sentencia = $this->con->prepare($sql);
        $sentencia->execute();

        return $sentencia;
    }

    public function ejecutarConParametros($sql, $parametros) {
        self::Conectar();

        $sentencia = $this->con->prepare($sql);

        foreach ($parametros as $campo => &$valor) {
            $sentencia->bindParam($campo, $valor);
        }

        $sentencia->execute();
        # $sentencia->debugDumpParams();

        return $sentencia;
    }

    public function insertar($sql, $parametros) {
        self::Conectar();
        
        $sentencia = $this->con->prepare($sql);

        foreach ($parametros as $campo => &$valor) {
            $sentencia->bindParam($campo, $valor);
        }
        
        $sentencia->execute();
        # $sentencia->debugDumpParams();
        
        return $this->con->lastInsertId();
    }
}