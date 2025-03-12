<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'tu_contraseña';
$db_name = 'registro_estudiantes';

$fecha = date("Y-m-d_H-i-s");
$backup_file = "backups/{$db_name}_$fecha.sql";

// Comando mysqldump
$command = "mysqldump --user={$db_user} --password={$db_pass} --host={$db_host} {$db_name} > {$backup_file}";

system($command);

// Eliminar backups antiguos (más de 30 días)
foreach (glob("backups/*.sql") as $archivo) {
    if (time() - filemtime($archivo) > 30 * 86400) {
        unlink($archivo);
    }
}