<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Bienvenue sur {cfg:site_name}</h1>
<p>
    In order for this service to operate it must retain some
    information about files, who can access them, and what has
    happened. Files will be automatically removed from the system when
    they expire and other retained information will be removed from
    the system and database after some amount of time has passed. This
    page allows you to see how long various pieces of information are
    retained by this installation.
</p>
<p>
    Veuillez noter que lorsqu'un dépôt est supprimé, tous les fichiers liés ainsi que les copies des emails envoyés seront aussi supprimés.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Ce site est configuré pour nettoyer l'espace disque des fichiers lorsqu'ils sont supprimés";
    echo "Nettoyer un fichier consiste à écrire plusieurs fois au même endroit sur le disque des données afin de vraiment supprimer toute trace du fichier";
    echo "Cela permet une meilleure confidentialité des données.</p>";
}
?>