<?php

$lang["_HELP_TEXT"] = '

<div>

<div align="left" style="padding:5px">

<h4>Connexion</h4> 
<ul>
    <li>Si vous ne voyez pas votre institution dans la liste des fournisseurs d\'Identit� (IdPs), ou si la connexion avec votre nom d\'utilisateur au sein de votre institution �choue, veuillez contacter votre service informatique local</li>
</ul>

<h4>T�l�chargements vers FileSender jusqu\'� 2 Gigaoctets (2GO) avec Adobe Flash</h4>
<ul>
	<li>Si vous �tes capable de regarder des vid�os YouTube, cette m�thode devrait fonctionner pour vous</li>
	<li>Vous avez besoin d\'un navigateur moderne qui tourne avec la version 10 (ou sup�rieure) de <a target="_blank" href="http://www.adobe.com/software/flash/about/">Adobe Flash</a></li>
	<li>FileSender va vous avertir si vous essayez de t�l�charger un fichier trop gros pour cette m�thode</li>
</ul>

<h4>T�l�chargements vers FileSender <i>de toutes tailles</i> avec HTML5</h4>
<ul>
    <li>Si vous voyez <img src="images/html5_installed.png" alt="green HTML5 tick" class="textmiddle" style="display:inline" /> dans le coin en haut � droite, cette m�thode fonctionne pour vous</li>
	<li>Vous avez besoin d\'un navigateur web tr�s r�cent qui supporte HTML5, la version la plus r�cente du "language du web".</li>
	<li>Pour l\'instant Firefox4 (ou sup�rieur) et Chrome sous Windows, Mac OSX et Linux devraient fonctionner.</li>
	<li>Veuillez utiliser le site web <a href="http://caniuse.com/#feat=fileapi" target="_blank">"When can I use..."</A> pour observer le progr�s d\'impl�mentation de la "HTML5 FileAPI" pour tous les navigateurs web majeurs.  En particulier, <a href="http://caniuse.com/#feat=filereader" target="_blank">"FileReader API"</A> et <A href="http://caniuse.com/#feat=bloburls" target="_blank">"Blob URLs"</A> doivent �tre marqu�s en vert clair (=support�s) pour qu\'un navigateur web supporte des fichiers plus gros que 2GO </li>
</ul>

<h4>T�l�chargements de FileSender de toutes tailles</h4>
<ul>
    <li>Vous avez besoin d\'un navigateur web r�cent, vous n\'avez besoin <b>ni</b> d\'Adobe Flash <b>ni</b> de HTML5 pour recevoir des fichiers de FileSender</li>
</ul>


<h4>Limites de cette installation FileSender</h4>
<ul>
    <li><strong>
      Nombre maximal de destinataires email: </strong>'. $config["max_email_recipients"].' adresses email (separ�es par virgule ou point-virgule)</li>
    <li><strong>Nombre maximal de fichiers par t�l�chargement :</strong> un fichier - pour t�l�charger plusieurs fichiers en m�me temps, \'zippez\' les d\'abord dans une seule archive </li>
    <li><strong>Taille maximale de fichier, pour Adobe Flash : </strong>'. formatBytes($config["max_flash_upload_size"]).' </li>
    <li><strong>Taille maximale de fichier, pour HTML5 : </strong>'. formatBytes($config["max_html5_upload_size"]).'</li>
    <li><strong>Nombre maximal de jours pour l\'expiration de fichiers / tickets : </strong>'. $config["default_daysvalid"].' </li>
</ul>
<p>Pour plus d\'informations, veuillez visiter <a href="http://www.filesender.org/">www.filesender.org</a></p>
</div>
</div>';

$lang["_ABOUT_TEXT"] = ' <div align="left" style="padding:5px">'. htmlentities($config['site_name']) .' est une installation de FileSender (<a rel="nofollow" href="http://www.filesender.org/">www.filesender.org</a>), d�velop� pour les besoins de la communaut� de l\'�ducation sup�rieure et de la recherche.</div>';

$lang["_AUPTERMS"] = "Conditions g�n�rales d\'utilisation ...";

?>
