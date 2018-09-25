<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Résumé quotidien de l'activité du dépôt

{alternative:plain}

Madame, Monsieur,

Veuillez trouver ci-dessous un résumé des téléchargements pour votre dépôt {transfer.id} (créé le {date:transfer.created}) :

{if:events}
{each:events as event}
  - Le destinataire {event.who} a téléchargé {if:event.what == "archive"}un groupe de fichiers{else}le fichier {event.what_name}{endif} le {datetime:event.when}
{endeach}
{else}
Aucun téléchargement
{endif}

Vous pourrez trouver plus de détails sur {transfer.link}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Veuillez trouver ci-dessous un résumé des téléchargements pour votre dépôt {transfer.id} (créé le {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Le destinataire {event.who} a téléchargé {if:event.what == "archive"}un groupe de fichiers{else}le fichier {event.what_name}{endif} le {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Aucun téléchargement
</p>
{endif}

<p>
    Vous pourrez trouver plus de détails sur <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
