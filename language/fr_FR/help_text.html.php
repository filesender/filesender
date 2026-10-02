<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Connexion</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i> Si vous ne voyez pas votre institution dans la liste des fournisseurs d'Identité (IdPs), ou si la connexion avec votre nom d'utilisateur au sein de votre institution échoue, veuillez contacter votre service informatique local.</li>
</ul>

<h3>Capacités de votre navigateur</h3>
<ul class="fa-ul">
    <li data-feature="html5"><i class="fa-li fa fa-caret-right"></i><img src="images/html5_installed.png" alt="Téléversement HTML5 disponible" /> Vous pouvez déposer des fichiers de toutes tailles jusqu'à {size:cfg:max_transfer_size} par dépôt.</li>
    <li data-feature="nohtml5"><i class="fa-li fa fa-caret-right"></i><img src="images/html5_none.png" alt="Téléversement HTML5 indisponible" /> Vous pouvez déposer des fichiers d'au maximum {size:cfg:max_legacy_file_size} chacun et jusqu'à {size:cfg:max_transfer_size} par dépôt.</li>
</ul>

<h3>Dépôts vers FileSender <strong>de toutes tailles</strong> avec HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Vous pourrez utiliser cette méthode si le symbole <img src="images/html5_installed.png" alt="Téléversement HTML5 disponible" /> est affiché ci-dessus.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Vous avez besoin d'un navigateur web récent qui supporte HTML5, la version la plus récente du "langage du web".</li>
    <li><i class="fa-li fa fa-caret-right"></i>Une version à jour de Firefox ou Chrome pour Windows, Mac OS X ou Linux suffit pour bénéficier de cette fonctionnalité.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Vous pourrez <strong>redémarrer</strong> un dépôt interrompu, il vous suffira de <strong>re-selectionner les mêmes fichiers</strong>.
        Assurez vous que les fichiers aient les <strong>mêmes noms et tailles</strong> que lors de votre dépôt initial.
        Une fois tous les fichiers du dépôt initial re-selectionnés <strong>votre dépôt redémarrera à la progression à laquelle il s'était arrêté</strong>.
    </li>
</ul>

<h3>Dépôts vers FileSender jusqu'à {size:cfg:max_legacy_file_size} par fichier sans HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>FileSender va vous avertir si vous essayez de télécharger un fichier trop gros pour cette méthode.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Le redémarrage n'est pas disponible avec cette méthode.</li>
</ul>

<h3>Téléchargements de FileSender de toutes tailles</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Vous avez besoin d'un navigateur web récent mais pas de HTML5 pour recevoir des fichiers de FileSender.</li>
</ul>


<h3>Caractéristiques de cette installation FileSender</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Nombre maximal de destinataires : </strong>{cfg:max_transfer_recipients} adresses email (séparées par virgule ou point-virgule)</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Nombre maximal de fichiers par dépôt :</strong> {cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Taille maximale de dépôt : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Taille maximale de fichier pour les navigateurs non-compatibles HTML5 : </strong>{size:cfg:max_legacy_file_size} </li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Nombre de jours pour l'expiration des dépôts : </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Nombre de jours pour l'expiration des invitations : </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid}) </li>
</ul>

<h3>Informations techniques</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> est basé sur le <a href="http://www.filesender.org/" target="_blank">logiciel FileSender</a>.
        FileSender indique si la méthode de dépôt basée sur HTML5 est disponible pour chaque navigateur spécifiquement.
        Ceci dépend principalement d'une fonctionnalité avancée du navigateur, en particulier la FileAPI de HTML5.
        Veuillez utiliser le site <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> pour avoir un aperçu du progrès de l'intégration de la FileAPI au sein des principaux navigateurs.
        Les fonctionnalités <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> et <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> doivent être affichées en vert clair (supportées) pour qu'un navigateur soit compatible pour des dépôts de fichiers de plus de {size:cfg:max_legacy_file_size}.
        Veuillez noter que même si Opera 12 est listé comme compatible il ne n'embarque pas à l'heure actuelle tout ce qui est nécessaire pour être compatible avec la méthode de dépôt HTML5 de FileSender.
    </li>
</ul>

<p>Pour plus d'informations, veuillez visiter <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
