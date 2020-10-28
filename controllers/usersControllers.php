<?php

function trace($test)
{
  print '<pre>';
  print_r($test);
  print '</pre>';
};

session_start();
date_default_timezone_set('America/Bogota');
setLocale(LC_ALL, "es_CO");

require_once "../models/users.php";

class userControllers extends user
{

  public function __construct()
  {
    parent::__construct();
  }

  public function parse($text)
  {
    $parsedText = str_replace(chr(10), "", $text);
    return str_replace(chr(13), "", $parsedText);
  }
}

if (isset($_POST["petition"])) {
  $petition = $_POST["petition"];
  $UC = new userControllers();

  $response = [
    "state" => false,
    "message" => "Hubo un error al procesar la petición"
  ];

  switch ($petition) {

    case 'logIn':

      $params = [
        ":username" => $_POST['userName'],
        ":password" => hash("sha256", $_POST['password']),
      ];

      if (isset($params[':username']) && isset($params[':password'])) {
        $consultUser = $UC->consultUser($params);

        if (count($consultUser) > 0) {

          $_SESSION['user'] = [
            "id" => $consultUser[0]['id'],
            "name" => strtoupper($consultUser[0]['name'])
          ];

          $response = [
            "state" => true,
            "message" => "Iniciando sesión"
          ];
        } else {
          $response = [
            "state" => false,
            "message" => "Error al consultar usuario."
          ];
        }
      } else {
        $response = [
          "state" => false,
          "message" => "Llene todos los campos."
        ];
      }

      echo json_encode($response);
      break;
  }
}
