<?php
require 'php/config/db.php'
class Estudiante
{
    private $nombre, $apellido, $id, $cedula, $fecha_nacimiento, $año, $seccion;

    public function __construct($nombre, $apellido, $id, $cedula, $fecha_nacimiento, $año, $seccion, $genero=null)
    {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->cedula = $cedula;
        $this->fecha_nacimiento = $fecha_nacimiento;
        $this->año = $año;    
        $this->seccion = $seccion;
        $this->genero = $genero;
        if ($id) {
            $this->id = $id;
        }

    }

    public function guardar()
    {
        global $mysqli;
        $sentencia = $mysqli->prepare("INSERT INTO estudiantes
            (nombre, apellido, cedula, fecha_nacimiento, año, seccion)
                VALUES
                (?, ?, ?, ?, ?, ?, ?");
        $sentencia->bind_param("ssssss", $this->nombre, $this->apellido, $this->cedula, $this->fecha_nacimiento, $this->año, $this->seccion, $this->genero);
        $sentencia->execute();
    }

    public static function obtener()
    {
        global $mysqli;
        $resultado = $mysqli->query("SELECT id, nombre, apellido, cedula, fecha_nacimiento, año, seccion, genero FROM estudiantes");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    public static function obtenerUno($id)
    {
        global $mysqli;
        $sentencia = $mysqli->prepare("SELECT id, nombre, apellido, cedula, fecha_nacimiento, año, seccion, genero FROM estudiantes WHERE id = ?");
        $sentencia->bind_param("i", $id);
        $sentencia->execute();
        $resultado = $sentencia->get_result();
        return $resultado->fetch_object();
    }
    public function actualizar()
    {
        global $mysqli;
        $sentencia = $mysqli->prepare("update estudiantes set nombre = ?, apellido = ?, cedula = ?, fecha_nacimiento = ?, año = ?, seccion = ?, genero = ? where id = ?");
        $sentencia->bind_param("ssssss", $this->nombre, $this->apellido, $this->cedula, $this->fecha_nacimiento, $this->año, $this->seccion, $this->genero, $this->id);
        $sentencia->execute();
    }

    public static function eliminar($id)
    {
        global $mysqli;
        $sentencia = $mysqli->prepare("DELETE FROM estudiantes WHERE id = ?");
        $sentencia->bind_param("i", $id);
        $sentencia->execute();
    }
}
