<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Invitation
subject: {guest.subject}

{alternative:plain}

Madame, Monsieur,

Veuillez trouver ci-dessous une invitation de {guest.user_email} pour déposer des fichiers sur {cfg:site_name}.

Lien de dépôt: {guest.upload_link}

Cette invitation est valable jusqu'au {date:guest.expires} après quoi elle sera automatiquement revoquée.

{if:guest.message}Message de {guest.user_email}: {guest.message}{endif}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Veuillez trouver ci-dessous une invitation de {guest.user_email} pour déposer des fichiers sur <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>


<p>
    Lien de dépôt: <a href="{guest.upload_link}">{guest.upload_link}</a>
</p>

<p>
    Cette invitation est valable jusqu'au {date:guest.expires} après quoi elle sera automatiquement revoquée.
</p>

{if:guest.message}
<p>
    Message de {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
