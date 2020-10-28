<?php
session_start();
date_default_timezone_set('America/Bogota');
setLocale(LC_ALL, "es_CO");

require_once "../models/usuarios.php";

class usuarioControllers extends usuario {

  public function __construct() {
    parent::__construct();
  }

  public function parse( $text ){
    $parsedText = str_replace(chr(10), "", $text);
    return str_replace(chr(13), "", $parsedText);
  }

  public function universal( $sql ) {
    $sentencia = $this->ejecutar( $sql );
    return $sentencia;
  }
}

if( isset($_POST["peticion"]) ) {
  $peticion = $_POST["peticion"];
  $uc = new usuarioControllers();

  $respuesta = [
    "exito" => false,
    "msj" => "Hubo un error al procesar la petición"
  ];

  switch($peticion) {
  //Agregar
    case 'agregar':
    //Variables a enviar en la variable PARAMS"
    $params = [
      ":nombre" => utf8_decode( $_POST['nombre'] ),
      ":apellido" => utf8_decode( $_POST['apellido'] ),
      ":tipoDocumento" => utf8_decode( $_POST['tipoDocumento'] ),
      ":numDocumento" => utf8_decode( $_POST['numDocumento'] ),
      ":correo" => utf8_decode( $_POST['correo'] ),
      ":celular" => utf8_decode( $_POST['celular'] ),
      ":contrasenia" => hash("sha256", $_POST["password"]),
      ":tipoContrato" => utf8_decode( $_POST['tipoContrato'] ),
      ":tipoUsuario" => utf8_decode( $_POST['tipoUsuario'] ),
      ":idRoles" => utf8_decode( $_POST['idRoles'] )
    ];

    $sqlGuardar = $uc->agregarUsuario( $params );

    if ( $sqlGuardar > 0 ) {
      $sql = "INSERT INTO horasinves_inst( horas, idUsu, idTrim ) VALUES ( '".$_POST['horasinv1']."', $sqlGuardar, 1 ),( '".$_POST['horasinv2']."', $sqlGuardar, 2 ),( '".$_POST['horasinv3']."', $sqlGuardar, 3 ),( '".$_POST['horasinv4']."', $sqlGuardar, 4 )";
      $query = $uc->universal( $sql );
      if ( $query ) {
        $h1 = intval( $_POST['horas1'] ) - intval( $_POST['horasinv1'] );
        $horas1 = [
          ":horas" => utf8_decode( $_POST['horas1'] ),
          ":horasDisp" => utf8_decode( $h1 ),
          ":idUsu" => $sqlGuardar,
          ":idTrim" => 1
        ];
        $h2 = intval( $_POST['horas2'] ) - intval( $_POST['horasinv2'] );
        $horas2 = [
          ":horas" => utf8_decode( $_POST['horas2'] ),
          ":horasDisp" => utf8_decode( $h2 ),
          ":idUsu" => $sqlGuardar,
          ":idTrim" => 2
        ];
        $h3 = intval( $_POST['horas3'] ) - intval( $_POST['horasinv3'] );
        $horas3 = [
          ":horas" => utf8_decode( $_POST['horas3'] ),
          ":horasDisp" => utf8_decode( $h3 ),
          ":idUsu" => $sqlGuardar,
          ":idTrim" => 3
        ];
        $h4 = intval( $_POST['horas4'] ) - intval( $_POST['horasinv4'] );
        $horas4 = [
          ":horas" => utf8_decode( $_POST['horas4'] ),
          ":horasDisp" => utf8_decode( $h4 ),
          ":idUsu" => $sqlGuardar,
          ":idTrim" => 4
        ];
        $sql1hora = $uc->agregarHoras( $horas1 );
        $sql2hora = $uc->agregarHoras( $horas2 );
        $sql3hora = $uc->agregarHoras( $horas3 );
        $sql4hora = $uc->agregarHoras( $horas4 );
        if ( $sql1hora && $sql2hora && $sql3hora && $sql4hora) {
          $respuesta = [
            "exito" => true,
            "msj" => "Usuario Agregado"
          ];
        } else {
          $respuesta = [
            "exito" => false,
            "msj" => "Inconvenientes al guardar horas"
          ];
        }
      } else {
        $respuesta = [
          "exito" => false,
          "msj" => "Inconvenientes al guardar horas investigación"
        ];
      }
    } else {
      $respuesta = [
        "exito" => false,
        "msj" => "Falla al guardar"
      ];
    }

    echo json_encode( $respuesta );
    break;

  //LISTAR
    case 'listar':

    $datos = $uc->listaUsuario();

    $data = "";
    foreach ( $datos as $row ) {
      $opcion =
      '<button data-idT=\"'.$row['id'].'\" type=\"button\" class=\"editar btn btn-info btn-raised btn-xs\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Editar\"><i class=\"fas fa-edit\"></i></button>
      <button data-idT=\"'.$row['id'].'\" type=\"button\" class=\"eliminar btn btn-danger btn-raised btn-xs\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Eliminar\"><i class=\"fas fa-trash-alt\"></i></button>';
      $ver =
      '<button data-idT=\"'.$row['id'].'\" type=\"button\" class=\"ver btn btn-info btn-raised btn-xs\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ver\"><i class=\"fas fa-eye\"></i></button>';

      $data.= '{
        "Nombre":"'.utf8_encode( $row['nombre'] ).'",
        "Apellido":"'.utf8_encode( $row['apellido'] ).'",
        "Tipo de documento":"'.utf8_encode( $row['tipoDoc'] ).'",
        "Número de documento":"'.utf8_encode( $row['numDocumento'] ).'",
        "Correo":"'.utf8_encode( $row['correo'] ).'",
        "Celular":"'.utf8_encode( $row['celular'] ).'",
        "Tipo de contrato":"'.utf8_encode( $row['tipoCon'] ).'",
        "Tipo de Usuario":"'.utf8_encode( $row['tipoUs'] ).'",
        "Rol":"'.utf8_encode( $row['nomrol'] ).'",
        "Horas del Instructor":"'.$uc->parse( $ver ).'",
        "Opciones":"'.$uc->parse( $opcion ).'"
      },';
    }

    $data = substr($data,0, strlen($data) - 1);

    echo '{"data":['.$data.']}';
    break;
//Consultas
    case 'consultar':
    $params = [
      ":id" => $_POST['id']
    ];

    $datos = $uc->consultarUsuario( $params );
    $horas = $uc->consultarHoras( $params );
      //Esta consulta primero muestra los datos en los campos-  NO PARA EDITAR
    if ( $datos && $horas ) {
      $usuariosD = [
        "id" => $datos[0]['id'],      //Array para que recorra cada uno de los campos
        "nombre" => utf8_encode( $datos[0]['nombre'] ),
        "apellido" => utf8_encode( $datos[0]['apellido'] ),
        "tipoDocumento" => utf8_encode( $datos[0]['tipoDocumento'] ),
        "numDocumento" => utf8_encode( $datos[0]['numDocumento'] ),
        "correo" => utf8_encode( $datos[0]['correo'] ),
        "celular" => utf8_encode( $datos[0]['celular'] ),
        "tipoContrato" => utf8_encode( $datos[0]['tipoContrato'] ),
        "tipoUsuario" => utf8_encode( $datos[0]['tipoUsuario'] ),
        "idRoles" => utf8_encode( $datos[0]['idRoles'] )
      ];
      $horaFinal = [];
      $horaFinalInv = [];
      foreach ($horas as $row) {
        array_push( $horaFinal , $row['horas'] );
        array_push( $horaFinalInv , $row['horaInv'] );
      }

      $respuesta = [
        "exito" => true,
        "msj" => $usuariosD,
        "horas" => $horaFinal,
        "horasinv" => $horaFinalInv
      ];
    } else {
      $respuesta = [
        "exito" => false,
        "msj" => "Inconvenientes en el aplicativo por favor intente más tarde"
      ];
    }

    echo json_encode( $respuesta );
    break;
    //Fin ----------------------------------------------- LISTAR

  //ACTUALIZAR
    case 'actualizar':
    $params = [
      ":nombre" => utf8_decode( $_POST['newnombre'] ),
      ":apellido" => utf8_decode( $_POST['newapellido'] ),
      ":tipoDocumento" => utf8_decode( $_POST['newtipoDocumento'] ),
      ":numDocumento" => utf8_decode( $_POST['newnumDocumento'] ),
      ":correo" => utf8_decode( $_POST['newcorreo'] ),
      ":celular" => utf8_decode( $_POST['newcelular'] ),
      ":tipoContrato" => utf8_decode( $_POST['newtipoContrato'] ),
      ":tipoUsuario" => utf8_decode( $_POST['newtipoUsuario'] ),
      ":idRoles" => utf8_decode( $_POST['newidRoles'] ),
      ":id" => $_POST['id']
    ];

    $datos = $uc->actualizarUsuario( $params );

    if ( $datos ) {

      $string = "<h4><strong>No se pudo actualizar las horas del Instructor en los Trimestres del año:</strong></h4>";
      $cont1 = 0;
      $cont2 = 0;
      $cont3 = 0;
      $cont4 = 0;
      
      $newh1 = intval( $_POST['newhoras1'] - $_POST["edit_horasinv1"] );
      $newh2 = intval( $_POST['newhoras2'] - $_POST["edit_horasinv2"] );
      $newh3 = intval( $_POST['newhoras3'] - $_POST["edit_horasinv3"] );
      $newh4 = intval( $_POST['newhoras4'] - $_POST["edit_horasinv4"] );
      
      $sql1hora = $uc->actualizarHoras( $_POST['id'],1, $newh1 );
      $sql2hora = $uc->actualizarHoras( $_POST['id'],2, $newh2 );
      $sql3hora = $uc->actualizarHoras( $_POST['id'],3, $newh3 );
      $sql4hora = $uc->actualizarHoras( $_POST['id'],4, $newh4 );

      if ( !$sql1hora ) {
        $string .= "<h4><i class='fas fa-check-double'></i> 1er Trimestre.</h4>";
        $cont1++;
      }
      if ( !$sql2hora ) {
        $string .= "<h4><i class='fas fa-check-double'></i> 2do Trimestre.</h4>";
        $cont2++;
      }
      if ( !$sql3hora ) {
        $string .= "<h4><i class='fas fa-check-double'></i> 3er Trimestre.</h4>";
        $cont3++;
      }
      if ( !$sql4hora ) {
        $string .= "<h4><i class='fas fa-check-double'></i> 4to Trimestre.</h4>";
        $cont4++;
      }
      $string .= "<h4>Debido a que el instructor tiene asignada más horas en comparación de las horas nuevas que se ingresarón.</h4>";
      if ( $cont1 == 0 && $cont2 == 0 && $cont3 == 0 && $cont4 == 0 ) {

        $sqlinv1hora = $uc->actuHorasInv( $_POST['id'], 1, $_POST["edit_horasinv1"] );
        $sqlinv2hora = $uc->actuHorasInv( $_POST['id'], 2, $_POST["edit_horasinv2"] );
        $sqlinv3hora = $uc->actuHorasInv( $_POST['id'], 3, $_POST["edit_horasinv3"] );
        $sqlinv4hora = $uc->actuHorasInv( $_POST['id'], 4, $_POST["edit_horasinv4"] );

        $respuesta = [
          "exito" => true,
          "msj" => "Instructor Actualizado Correctamente",
          "horas" => true
        ];
      }else {
        if ( $cont1 == 0) {
          $sqlinv1hora = $uc->actuHorasInv( $_POST['id'], 1, $_POST["edit_horasinv1"] );
        }
        if ( $cont2 == 0) {
          $sqlinv2hora = $uc->actuHorasInv( $_POST['id'], 2, $_POST["edit_horasinv2"] );
        }
        if ( $cont3 == 0) {
          $sqlinv3hora = $uc->actuHorasInv( $_POST['id'], 3, $_POST["edit_horasinv3"] );
        }
        if ( $cont4 == 0) {
          $sqlinv4hora = $uc->actuHorasInv( $_POST['id'], 4, $_POST["edit_horasinv4"] );
        }
        $respuesta = [
          "exito" => true,
          "msj" => "Instructor Actualizado Correctamente",
          "horas" => false,
          "string" => $string
        ];
      }

    } else {
      $respuesta = [
        "exito" => false,
        "msj" => "Error al actualizar"
      ];
    }

    echo json_encode( $respuesta );
    break;
    //FIN ---------------------------- ACTUALIZAR

    //Eliminar
  case 'eliminar':
    $params = [
      ":id" => $_POST['id']
    ];

    $datos = $uc->eliminarUsuario( $params );

    if ( $datos ) {
      $respuesta = [
        "exito" => true,
        "msj" => "Instructor Eliminado"
      ];
    } else {
      $respuesta = [
        "exito" => false,
        "msj" => "Error al Eliminar"
      ];
    }

    echo json_encode( $respuesta );
    break;

    case 'verHoras':
      $string = "";
      $params = [
        ":id" => $_POST['id']
      ];
      $datos = $uc->consultarHoras( $params );
      foreach ( $datos as $row ) {
        $string .= "<tr>";
        if ( $row['idTrim'] == 1 ) {
          $string .= "<th scope='row'>1er Trimestre</th>";
        } elseif ( $row['idTrim'] == 2 ) {
          $string .= "<th scope='row'>2do Trimestre</th>";
        } elseif ( $row['idTrim'] == 3 ) {
          $string .= "<th scope='row'>3er Trimestre</th>";
        } elseif ( $row['idTrim'] == 4 ) {
          $string .= "<th scope='row'>4to Trimestre</th>";
        }
        $string .= "<td>".$row['horas']." Horas</td> 
        <td>".$row['horasDisp']." Horas</td>
        <td>".$row['horaInv']." Horas</td>
        </tr>";
      }

      if ( $datos ) {
        $respuesta = [
          "exito" => true,
          "msj" => $string
        ];
      } else {
        $respuesta = [
          "exito" => false,
          "msj" => "Inconvenientes en el sistema por favor intente más tarde"
        ];
      }

      echo json_encode( $respuesta );
    break;

    //actualizar instructor
    case 'actualizarInst':
      $params = [
        ":correo" => utf8_decode( $_POST['newCorreo'] ),
        ":celular" => utf8_decode( $_POST['newCelular'] ),
        ":tipoContrato" => utf8_decode( $_POST['newTipoContrato'] ),
        ":id" => $_POST['id']
      ];
      
      $datos = $uc->actualizarInstructor( $params );
      
      if ( $datos ) {
        $respuesta = [
          "exito" => true,
          "msj" => "Instructor Actualizado Correctamente"
        ];
      } else {
        $respuesta = [
          "exito" => false,
          "msj" => "Inconveniente al actualizar sus datos"
        ];
      }
      
      echo json_encode( $respuesta );
    break;

    //actualizar instructor
    case 'actualizarInst':
        $params = [
          ":correo" => utf8_decode( $_POST['newCorreo'] ),
          ":celular" => utf8_decode( $_POST['newCelular'] ),
          ":tipoContrato" => utf8_decode( $_POST['newTipoContrato'] ),
          ":id" => $_POST['id']
        ];
    
        $datos = $uc->actualizarInstructor( $params );
    
        if ( $datos ) {
          $respuesta = [
            "exito" => true,
            "msj" => "Instructor Actualizado Correctamente"
          ];
        } else {
          $respuesta = [
            "exito" => false,
            "msj" => "Inconveniente al actualizar sus datos"
          ];
        }
    
      echo json_encode( $respuesta );
    break;
    //actualizar contraseña
    case 'actualizarContrasena':
      //Variable de mensaje vacia
        $msg = [
          "exito" => false,
          "href" => null,
          "msj" => null
        ];
      //Autenticar un usuario
      if(isset($_POST['actualContrasena']) && isset($_POST['newContrasena']) && isset($_POST['confirmarContrasena']) ){
        
        $actualContrasena = $_POST['actualContrasena'];
        $confirmActualContrasena = hash("sha256",utf8_encode($_POST[ 'actualContrasena']));
        $newContrasena = $_POST['newContrasena'];
        $confirmarContrasena = $_POST['confirmarContrasena'];
        $idUs = $_SESSION['usuario']['id'];

        $respArray = [
          ':contrasenia' => $confirmActualContrasena,
          ':id' => $idUs
        ];

        $respContrasena = $uc->validarContrasena($respArray); 
        $respCon2 = $respContrasena[0]['contUsuario'];

        if ($respCon2 == 1 ) {
            if ($newContrasena == $confirmarContrasena) {

              $newContrasena = hash("sha256",utf8_encode($confirmarContrasena));

              $paramsCont =[
                ':contrasenia' => $newContrasena,
                ':id' => $idUs
              ];
              $actCont = $uc->cambiarContrasena($paramsCont);
              if ($actCont) {
                $msg = [
                  "exito" => true,
                  "href" => null,
                  "msj" => "¡Contraseña actualizada correctamente!"
                ];
              } else {
                $msg = [
                  "exito" => false,
                  "href" => null,
                  "msj" => "¡Hubo un problema al actualizar la contraseña!"
                ];
              }
            } else {
              $msg = [
                "exito" => false,
                "href" => null,
                "msj" => "¡La contraseña nueva no coinciden!"
              ];
            }
        } else {
          $msg = [
            "exito" => false,
            "href" => null,
            "msj" => "¡Contraseña no valida!"
          ];
        }
        
        
      } else {
        $msg = [
            "exito" => false,
            "href" => null,
            "msj" => "¡El usuario o contraseña son incorrectos!"
        ];
      }

          echo json_encode($msg);
    break;



    default:
      # code...
    break;
  }
}