<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Welkom bij {cfg:site_name}</h1>
<p>
Om deze service te laten werken moet het informatie kunnen opslaan betreffende bestanden; wie er bij mag en wat er mee gebeurd is. Bestanden worden automatisch verwijderd van het systeem wanneer ze verlopen zijn. Deze pagina laat je zien hoe lang dergelijke informatie opgeslagen wordt door deze installatie.
</p>
<p>
Wanneer een transfer is verwijderd, worden alle gerelateerde bestanden en bijbehorende e-mails ook verwijderd.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Deze site is ingesteld om bestanden te shredden na verwijderen. ";
    echo "Shredden is het proces waarbij de fysieke plek van de bestanden overschreven wordt";
    echo " zodat de data daadwerkelijk verdwijnt. ";
    echo "Dit geeft de gebruikers van deze dienst extra privacy.</p>";
}
?>