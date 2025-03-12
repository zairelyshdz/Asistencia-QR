<?php
include_once "conexion.php";
include_once "Estudiante.php";
$estudiante = new Estudiante($_POST["nombre"], $_POST["apellido"], $_POST["id"], $_POST["cedula"], $_POST["edad"], $_POST["año"], $_POST["seccion"] );
$estudiante->actualizar();
header("Location: mostrar_estudiantes.php");
