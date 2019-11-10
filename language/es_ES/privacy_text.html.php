<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Bienvenido a {cfg:site_name}</h1>
<p>
    Para que este servicio funcione, debe retener
    información sobre archivos, quién puede acceder a ellos y qué sucedió 
    Luego de que haya transcurrido cierto tiempo, los archivos se eliminarán automáticamente del sistema y la base de datos cuando expiren junto con toda información retenida. 
    Esta página le permite ver cuánto tiempo la información es retenida por la instalación.
</p>
<p>
    Tenga en cuenta que cuando se elimina una transferencia, todos los archivos relacionados son
    también eliminados junto con las copias de cualquier correo electrónico que se haya enviado
    y que se relacionan con la transferencia.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Este sitio está configurado para destruir los archivos cargados cuando se eliminen. ";
    echo "Destruir un archivo implica escribir datos en la misma ubicación en el disco";
    echo " muchas veces para  realmente eliminar los datos del usuario del sistema. ";
    echo "Esto proporciona privacidad adicional para los usuarios de este servicio.</p>";
}
?>