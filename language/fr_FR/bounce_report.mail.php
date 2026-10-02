<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Erreur lors de la délivrance du message

{alternative:plain}

Madame, Monsieur,

Certains de vos destinataires n'ont pas pû reçevoir votre message :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Dépôt #{bounce.target.transfer.id} : destinataire {bounce.target.email} le {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Invité {bounce.target.email} le {datetime:bounce.date}
{endif}
{endeach}

Vous pourrez trouver plus de détails sur {cfg:site_url}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Certains de vos destinataires n'ont pas pû reçevoir votre message :
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Dépôt #{bounce.target.transfer.id}</a> : destinataire {bounce.target.email} le {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Invité {bounce.target.email} le {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Vous pourrez trouver plus de détails sur <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
