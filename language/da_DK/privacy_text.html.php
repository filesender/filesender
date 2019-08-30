<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Velkommen til {cfg:site_name}</h1>
<p>
For at tjenesten her kan fungere, må den gemme visse oplysninger om filerne, hvem der kan tilgå dem, og hvad der er sket. Filer slettes automatisk fra systemet når de udløber; og andre gemte oplysninger vil blive fjernet fra systemet og databasen efter et stykke tid. Her på siden kan man se hvor længe de forskellige typer oplysninger og data gemmes i den her installation af FileSender. 
</p>
<p>
Læg mærke til at sletning af en overførsel medfører samtidig sletning af alle tilhørende filer, inklusive kopier af e-mails som er blevet afsendt i forbindelse med overførslen.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Sitet her er sat op til at makulere uploadede filer når de slettes. ";
    echo "Makulering af en fil indebærer at der skrives data et stort antal gange ind over det område på disken hvor filen lå, for at man kan være sikker på at brugerdata forsvinder fra systemet.";
    echo "Det giver øget privatlivsbeskyttelse for folk som bruger tjenesten her.</p>";
}
?>