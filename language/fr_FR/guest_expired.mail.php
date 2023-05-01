<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Invitation de dépôt expirée

{alternative:plain}

Madame, Monsieur,

L' invitation à déposer des fichiers de la part de {guest.user_email} a expiré.

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    L' invitation à déposer des fichiers de la part de <a href="mailto:{guest.user_email}">{guest.user_email}</a> a expiré.
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
