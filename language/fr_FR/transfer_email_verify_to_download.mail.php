<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<?php 
// ATTENTION, il s'agit d'un fichier en lecture seule créé par les scripts d'importation
// ATTENTION
// ATTENTION, les modifications apportées à ce fichier seront bloquées
// ATTENTION
// ATTENTION, Veuillez faire les changements sur poeditor au lieu d'ici
// 
// 
?>
sujet : {cfg:nom_du_site} : Code de vérification unique pour le téléchargement

{alternative:plain}

Bonjour,

Quelqu'un (probablement vous) a demandé un code de vérification à usage unique afin de 
télécharger un fichier qui a été mis à votre disposition sur le service {cfg:nom_du_site}.
Voici votre code de vérification pour télécharger ce transfert :

{verificationCode}


{alternative:html}

<p>
    Bonjour,
</p>

<p>
Quelqu'un (probablement vous) a demandé un code de vérification à usage unique afin de 
télécharger un fichier qui a été mis à votre disposition sur le service {cfg:nom_du_site}.
Voici votre code de vérification pour télécharger ce transfert :
</p>
<p>
{verificationCode}
</p>

<p>
    Meilleures salutations,<br />
    {cfg:site_name}
</p>