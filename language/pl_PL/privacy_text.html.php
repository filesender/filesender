<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Witamy w usłudze {cfg:site_name}</h1>
<p>
Aby ta usługa mogła działać, musi przechowywać pewne informacje o plikach, kto może uzyskać do nich dostęp i co się stało. Pliki zostaną automatycznie usunięte z systemu po ich wygaśnięciu, a zachowane informacje zostaną usunięte z systemu i bazy danych po upływie pewnego czasu. Ta strona pozwala zobaczyć, jak długo różne informacje są przechowywane przez tę instalację.
</p>
<p>
Pamiętaj, że po usunięciu transferu wszystkie powiązane z nim pliki są również usuwane wraz z kopiami wysłanych emaili, dotyczących transferu.
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Ta witryna jest skonfigurowana do niszczenia przesłanych plików po ich usunięciu. ";
    echo "Niszczenie pliku wymaga zapisania danych w tej samej lokalizacji na dysku";
    echo " wiele razy, tak aby naprawdę usunąć dane użytkownika z systemu. ";
    echo "Zapewnia to dodatkową prywatność użytkownikom tej usługi.</p>";
}
?>

