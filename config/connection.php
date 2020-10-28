<?php
date_default_timezone_set('America/Bogota');
setLocale(LC_ALL, "es_CO");

class Connection {
    private $data;
    public $connection;

    public function __construct() {
        include __DIR__."/data.php";

        $this->data = $CONNECTION["developing"];
    }

    public function Connect() {
        $str_con = "mysql:host=". $this->data["host"];
        
        if($this->data["port"] > 0) {
            $str_con .= ":". $this->data["port"];
        }

        $str_con .= ";dbname=". $this->data["db"];

        try {
            $this->con = new PDO($str_con, $this->data["user"], $this->data["pass"]);

        } catch(Exception $e) {
            echo "<b>Hubo un error al conectar</b><br>";
        }       
    }

    public function Disconnect() {
        $this->connection = null;
    }

    public function buildParams($params) {
        try {
            # Parametrizacion de Consulta
            $fields_where = [];
            $params_sent = [];

            foreach($params as $array) {
                switch( count($array) ) {
                    case 4:
                    array_push(
                        $fields_where, 
                        "$array[0] $array[1] $array[2]"
                    );
                    break;

                    case 3:
                    array_push(
                        $fields_where, 
                        "$array[0] = $array[1]"
                    );
                    break;

                    case 2:
                    $c = str_replace(":", "", $array[0]);
                    array_push(
                        $fields_where, 
                        "areas.id = :$c"
                    );
                    break;
                }

                $key_value = array_slice($array, -2, 2);
                $params_sent[$key_value[0]] = $key_value[1];
            }

            return [
                "sql_params" => $fields_where,
                "pdo_params" => $params_sent
            ];
        } catch(Exception $e) {
            echo "Error:<pre>";
            print_r($e);
            echo "</pre>";
        }
    }

    public function run($sql) {
        self::Connect();

        $sentence = $this->con->prepare($sql);
        $sentence->execute();

        return $sentence;
    }

    public function runWithParams($sql, $params) {
        self::Connect();

        $sentence = $this->con->prepare($sql);

        foreach ($params as $field => &$value) {
            $sentence->bindParam($field, $value);
        }

        $sentence->execute();
        # $sentence->debugDumpParams();

        return $sentence;
    }

    public function insert($sql, $params) {
        self::Connect();
        
        $sentence = $this->con->prepare($sql);

        foreach ($params as $field => &$value) {
            $sentence->bindParam($field, $value);
        }
        
        $sentence->execute();
        # $sentence->debugDumpParams();
        
        return $this->con->lastInsertId();
    }
}