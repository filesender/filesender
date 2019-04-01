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
    Afin que ce service puisse fonctionner, celui-ci doit stocker des informations
    sur les fichiers, qui peut y accéder, et les actions effectuées.
    Les fichiers sont automatiquement supprimés du système et de la base de 
    données après un certain temps.
    Cette page vous permet de voir pendant combien de temps ces informations 
    sont stockées sur cette installation.
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