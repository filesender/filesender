<?php

$lang["_HELP_TEXT"] = '

<div>

<div align="left" style="padding:5px">

<h4>Connexion</h4> 
<ul>
    <li>Si vous ne voyez pas votre institution dans la liste des fournisseurs d\'Identité (IdPs), ou si la connexion avec votre nom d\'utilisateur au sein de votre institution échoue, veuillez contacter votre service informatique local</li>
</ul>

<h4>Téléchargements vers FileSender jusqu\'à 2 Gigaoctets (2GO) avec Adobe Flash</h4>
<ul>
	<li>Si vous êtes capable de regarder des vidéos YouTube, cette méthode devrait fonctionner pour vous</li>
	<li>Vous avez besoin d\'un navigateur moderne qui tourne avec la version 10 (ou supérieure) de <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash</a></li>
	<li>FileSender va vous avertir si vous essayez de télécharger un fichier trop gros pour cette méthode</li>
</ul>

<h4>Téléchargements vers FileSender <i>de toutes tailles</i> avec HTML5</h4>
<ul>
    <li>Si vous voyez <img src="images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> dans le coin en haut à droite, cette méthode fonctionne pour vous</li>
	<li>Vous avez besoin d\'un navigateur web très récent qui supporte HTML5, la version la plus récente du "language du web".</li>
	<li>Pour l\'instant Firefox4 (ou supérieur) et Chrome sous Windows, Mac OSX et Linux devraient fonctionner.</li>
	<li>Veuillez utiliser le site web <a href="http://caniuse.com/#feat=fileapi" target="_blank">"When can I use..."</A> pour observer le progrès d\'implémentation de la "HTML5 FileAPI" pour tous les navigateurs web majeurs.  En particulier, <a href="http://caniuse.com/#feat=filereader" target="_blank">"FileReader API"</A> et <A href="http://caniuse.com/#feat=bloburls" target="_blank">"Blob URLs"</A> doivent être marqués en vert clair (=supportés) pour qu\'un navigateur web supporte des fichiers plus gros que 2GO </li>
</ul>

<h4>Téléchargements de FileSender de toutes tailles</h4>
<ul>
    <li>Vous avez besoin d\'un navigateur web récent, vous n\'avez besoin <b>ni</b> d\'Adobe Flash <b>ni</b> de HTML5 pour recevoir des fichiers de FileSender</li>
</ul>


<h4>Limites de cette installation FileSender</h4>
<ul>
    <li><strong>
      Nombre maximal de destinataires email: </strong>'. $config["max_email_recipients"].' adresses email (separées par virgule ou point-virgule)</li>
    <li><strong>Nombre maximal de fichiers par téléchargement :</strong> un fichier - pour télécharger plusieurs fichiers en même temps, \'zippez\' les d\'abord dans une seule archive </li>
    <li><strong>Taille maximale de fichier, pour Adobe Flash : </strong>'. formatBytes($config["max_flash_upload_size"]).' </li>
    <li><strong>Taille maximale de fichier, pour HTML5 : </strong>'. formatBytes($config["max_html5_upload_size"]).'</li>
    <li><strong>Nombre maximal de jours pour l\'expiration de fichiers / tickets : </strong>'. $config["default_daysvalid"].' </li>
</ul>
<p>Pour plus d\'informations, veuillez visiter <a href="http://www.filesender.org/">www.filesender.org</a></p>
</div>
</div>';

$lang["_ABOUT_TEXT"] = ' <div align="left" style="padding:5px">'. htmlentities($config['site_name']) .' est une installation de FileSender (<a rel="nofollow" href="http://www.filesender.org/">www.filesender.org</a>), dévelopé pour les besoins de la communauté de l\'éducation supérieure et de la recherche.</div>';

$lang["_AUPTERMS"] = "Conditions générales d\'utilisation ...";

?>
