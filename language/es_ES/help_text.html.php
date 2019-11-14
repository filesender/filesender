<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3> Iniciar sesión </h3>
<ul class = "fa-ul">
    <li> <i class = "fa-li fa fa-caret-right"> </i> Inicie sesión a través de uno de los proveedores de identidad enumerados utilizando su cuenta institucional estándar. Si no ve a su institución en la lista, o su inicio de sesión falla, comuníquese con su soporte de TI local </li>
</ul>

<h3> Las características de su navegador </h3>
<ul class = "fa-ul">
    <li data-feature = "html5"> <img src = "images / html5_installed.png" alt = "Carga HTML5 habilitada" /> Puede cargar archivos de cualquier tamaño hasta {tamaño: cfg: max_transfer_size} por transferencia. < / li>
    <li data-feature = "nohtml5"> <img src = "images / html5_none.png" alt = "Carga HTML5 deshabilitada" /> Puede cargar archivos como máximo {size: cfg: max_legacy_file_size} cada uno y hasta {tamaño : cfg: max_transfer_size} por transferencia. </li>
</ul>

<h3> Subidas de <i> cualquier tamaño </i> con HTML5 </h3>
<ul class = "fa-ul">
    <li> <i class = "fa-li fa fa-caret-right"> </i> Podrá utilizar este método si se carga <img src = "images / html5_installed.png" alt = "HTML5 el signo "/> habilitado se muestra arriba </li>
    <li> <i class = "fa-li fa fa-caret-right"> </i> Para habilitar esta funcionalidad, simplemente use un navegador actualizado que admita HTML5, la última versión del "idioma de la web". </li>
    <li> <i class = "fa-li fa fa-caret-right"> </i> Se sabe que las versiones actualizadas de Firefox y Chrome en Windows, Mac OS X y Linux funcionan. </li>
    <li> <i class = "fa-li fa fa-caret-right"> </i>
        Puede <strong> reanudar </strong> una carga interrumpida o cancelada. Para reanudar una carga, simplemente <strong> envíe exactamente los mismos archivos </strong> nuevamente.
        Asegúrese de que los archivos tengan los <strong> mismos nombres y tamaños </strong> que antes.
        Cuando comience la carga, debería notar que la barra de progreso salta al lugar donde se detuvo la carga y continúa desde allí.
    </li>
</ul>

<h3> Sube hasta {size: cfg: max_legacy_file_size} por archivo sin HTML5 </h3>
<ul class = "fa-ul">
    <li> <i class = "fa-li fa fa-caret-right"> </i> FileSender le advertirá si intenta cargar un archivo que es demasiado grande para este método. </li>
    <li> <i class = "fa-li fa fa-caret-right"> </i> Reanudar cargas no es compatible con este método. </li>
</ul>

<h3> Descargas de cualquier tamaño </h3>
<ul class = "fa-ul">
    <li> <i class = "fa-li fa fa-caret-right"> </i> Cualquier navegador moderno funcionará bien, no se requiere nada especial para las descargas </li>
</ul>

<h3> Restricciones de servicio configurados </h3>
<ul class = "fa-ul">
    <li> <i class = "fa-li fa fa-caret-right"> </i> <strong> Número máximo de destinatarios: </strong> {cfg: max_transfer_recipients} direcciones de correo electrónico separadas por una coma o punto y coma </li>
    <li> <i class = "fa-li fa fa-caret-right"> </i> <strong> Número máximo de archivos por transferencia: </strong> {cfg: max_transfer_files} </li>
    <li> <i class = "fa-li fa fa-caret-right"> </i> <strong> Tamaño máximo por transferencia: </strong> {tamaño: cfg: max_transfer_size} </li>
    <li> <i class = "fa-li fa fa-caret-right"> </i> <strong> Tamaño máximo de archivo por archivo para navegadores que no son HTML5: </strong> {tamaño: cfg: max_legacy_file_size} </ li>
    <li> <i class = "fa-li fa fa caret-right"> </i> <strong> Días de vencimiento de la transferencia: </strong> {cfg: default_transfer_days_valid} (máx. {cfg: max_transfer_days_valid}) </ li>
    <li> <i class = "fa-li fa fa-caret-right"> </i> <strong> Días de vencimiento de invitados: </strong> {cfg: default_guest_days_valid} (máx. {cfg: max_guest_days_valid}) </ li>
</ul>

<h3> Detalles técnicos </h3>
<ul class = "fa-ul">
    <li> <i class = "fa-li fa fa-caret-right"> </i>
        <strong> {cfg: site_name} </strong> utiliza el <a href="http://www.filesender.org/" target="_blank"> software FileSender </a>.
        FileSender indica si el método de carga HTML5 es compatible o no con un navegador en particular.
        Esto depende principalmente de la disponibilidad de la funcionalidad avanzada del navegador, en particular el HTML5 FileAPI.
        Utilice el <a href="http://caniuse.com/fileapi" target="_blank"> "¿Cuándo puedo usar ..." </a> sitio web para supervisar el progreso de la implementación de HTML5 FileAPI para todos los principales navegadores .
        En particular, soporte para <a href="http://caniuse.com/filereader" target="_blank"> API FileReader </a> y <a href = "http://caniuse.com/bloburls" target = " _blank "> URL de blob </a> debe ser de color verde claro (= compatible) para que un navegador admita cargas superiores a {size: cfg: max_legacy_file_size}.
        Tenga en cuenta que, aunque Opera 12 figura en la lista para admitir HTML5 FileAPI, actualmente no admite todo lo necesario para admitir el uso del método de carga HTML5 en FileSender.
    </li>
</ul>

<p> Para obtener más información, visite <a href="http://www.filesender.org/" target="_blank"> www.filesender.org </a> </p>