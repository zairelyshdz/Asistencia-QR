<?php

$servidor = 'localhost';
$usuario = 'root';
$contraseña = '';
$dbname = 'registro_asistencia';

//se crea la conexion usando la función mysqli
$conexion = mysqli_connect($servidor, $usuario, $contraseña, $dbname);

//se verifica la conexión
if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}

//se hace una consulta a la base de datos usando la función jquery
$sql = "SELECT * FROM estudiantes";//creamos la consulta
$resultado = mysqli_query($conexion, $sql);//ejecutamos la consulta y guardamos el resultado en la variable $resultado

//se cierra la conexión
//mysqli_close($conexion);

?>