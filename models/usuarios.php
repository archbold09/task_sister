<?php
require_once __DIR__."/../conf/conexion.php";
/**
 * Modelo de tiempo
 */
class usuario extends Conexion {
	private $listado;

	function __construct() {
		parent::__construct();
	}

//Listar
	public function listaUsuario() {
		$sentencia = $this->ejecutar("SELECT us.*, sub.nombre as tipoDoc ,sub2.nombre as tipoCon , sub3.nombre as tipoUs, roles.nombre as nomrol
			FROM usuarios AS us
			INNER JOIN subitem AS sub ON us.tipoDocumento = sub.id
			INNER JOIN subitem AS sub2 ON us.tipoContrato = sub2.id
			INNER JOIN subitem AS sub3 ON us.tipoUsuario = sub3.id
			INNER JOIN roles ON us.idRoles = roles.id
			WHERE
			us.estado = 16 AND
			us.idRoles = 2");

		$this->listado = $sentencia->fetchAll( PDO::FETCH_ASSOC );
		return $this->listado;
	}


//Consulta
	public function consultarUsuario( $params ) {
		$sentencia = $this->ejecutarConParametros("SELECT * FROM usuarios WHERE id = :id",$params);
		$this->listado = $sentencia->fetchAll( PDO::FETCH_ASSOC );

		return $this->listado;
	}

	public function consultarHoras( $params ) {
		$sentencia = $this->ejecutarConParametros("SELECT hi.*, hii.horas as horaInv, hii.idTrim as trimid FROM horas_inst as hi 
			INNER JOIN horasinves_inst as hii ON hii.idUsu = hi.idUsu AND hii.idTrim = hi.idTrim
			WHERE 
			hi.idUsu = :id 
			ORDER BY hi.idTrim ASC, trimid ASC",$params);
		$this->listado = $sentencia->fetchAll( PDO::FETCH_ASSOC );

		return $this->listado;
	}

//Agregar
	public function agregarHoras( $params ) {
		$sentencia = $this->ejecutarConParametros("INSERT INTO horas_inst ( horas, horasDisp, idUsu, idTrim ) VALUES ( :horas, :horasDisp, :idUsu, :idTrim  )",$params);
		return $sentencia;
	}

	public function agregarUsuario( $params ) {
		$sentencia = $this->insertar("INSERT INTO usuarios ( nombre, apellido, tipoDocumento, numDocumento, correo, celular, contrasenia, tipoContrato,tipoUsuario, idRoles, estado )
			VALUES ( :nombre,:apellido, :tipoDocumento, :numDocumento, :correo, :celular, :contrasenia, :tipoContrato, :tipoUsuario, :idRoles, 16  )",$params);
		return $sentencia;
	}

//Actualizar
	public function actualizarUsuario( $params ) {
		$sentencia = $this->ejecutarConParametros("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, tipoDocumento = :tipoDocumento, numDocumento = :numDocumento, correo = :correo, celular = :celular, tipoContrato = :tipoContrato, tipoUsuario = :tipoUsuario, idRoles = :idRoles
			WHERE id = :id",$params);
		return $sentencia;
	}

	public function actualizarHoras( $idUsu,$idTrim,$horas ) {
		$resp = false;
		$sentencia = $this->ejecutar( "SELECT horas , horasDisp FROM horas_inst WHERE
			idUsu = ".$idUsu." AND idTrim = ".$idTrim );
		$sentencia = $sentencia->fetchAll( PDO::FETCH_ASSOC );
		if ( $sentencia[0]['horas'] == $sentencia[0]['horasDisp'] ) {
			$sql = $this->ejecutar( "UPDATE horas_inst SET horas = '".$horas."' , horasDisp = '".$horas."' WHERE
				idUsu = ".$idUsu." AND idTrim = ".$idTrim );
			$resp = true;
		} else {
			if ( $horas > $sentencia[0]['horas'] ) {
				$horaRes = intval($horas) - intval($sentencia[0]['horas']);
				$suma = intval($horaRes) + intval($sentencia[0]['horasDisp']);
				$sql = $this->ejecutar( "UPDATE horas_inst SET horas = '".$horas."' , horasDisp = '".$suma."' WHERE
					idUsu = ".$idUsu." AND idTrim = ".$idTrim );
				$resp = true;
			} else {
				$horaRes = intval($sentencia[0]['horas']) - intval($sentencia[0]['horasDisp']);
				$resta = intval($horas) - $horaRes;
				if ( $resta >= 0 ) {
					$sql = $this->ejecutar( "UPDATE horas_inst SET horas = '".$horas."' , horasDisp = '".$resta."' WHERE
						idUsu = ".$idUsu." AND idTrim = ".$idTrim );
					$resp = true;
				} else {
					$resp = false;
				}
			}
		}
		return $resp;
	}

	public function actuHorasInv( $idUsu, $Trim, $horas ){
		$sentencia = $this->ejecutar("UPDATE horasinves_inst SET horas = $horas 
			WHERE 
			idUsu = $idUsu AND
			idTrim = $Trim");
		return $sentencia;
	}


	//Eliminar
	public function eliminarUsuario( $params ) {
		$sentencia = $this->ejecutarConParametros("UPDATE usuarios SET estado = 17 WHERE id = :id",$params);
		return $sentencia;
	}


	//Listar documentos
	public function listaDoc() {

		$sentencia = $this->ejecutar("SELECT * FROM subitem WHERE idItem = 2");
		$this->listado = $sentencia->fetchAll( PDO::FETCH_ASSOC );

		return $this->listado;
	}
	//Listar Contrato
	public function listaCon() {

		$sentencia = $this->ejecutar("SELECT * FROM subitem WHERE idItem = 3");
		$this->listado = $sentencia->fetchAll( PDO::FETCH_ASSOC );

		return $this->listado;
	}
	//Listar Trabajo
	public function listaTrab() {

		$sentencia = $this->ejecutar("SELECT * FROM subitem WHERE idItem = 4");
		$this->listado = $sentencia->fetchAll( PDO::FETCH_ASSOC );

		return $this->listado;
	}
	//Listar Rol
	public function listaRol() {

		$sentencia = $this->ejecutar("SELECT us.* , item.nombre as rol
			FROM usuarios as us
			INNER JOIN roles as item ON us.idRoles = item.id");

		$this->listado = $sentencia->fetchAll( PDO::FETCH_ASSOC );

		return $this->listado;
	}
	//actualizar datos instructor

	public function actualizarInstructor( $params ) {
		$sentencia = $this->ejecutarConParametros("UPDATE usuarios SET tipoContrato = :tipoContrato, correo = :correo, celular = :celular
			WHERE id = :id",$params);
		return $sentencia;
	}
	//validar contraseña
	public function validarContrasena( $params ){
		$sentencia = $this->ejecutarConParametros("SELECT COUNT(*) as contUsuario FROM usuarios
		 WHERE contrasenia = :contrasenia AND id = :id  ",$params);
		$this->listado = $sentencia->fetchAll( PDO::FETCH_ASSOC );
		return $this->listado;
	}
	//modificar contraseña vieja
	public function cambiarContrasena( $params ){
		$sentencia = $this->ejecutarConParametros("UPDATE usuarios SET 
		contrasenia = :contrasenia
		WHERE id = :id",$params);
		return $sentencia;
	}
}
?>