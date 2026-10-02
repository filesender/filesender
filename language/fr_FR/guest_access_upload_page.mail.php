<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Un invité a accédé à la page de dépôt

{alternative:plain}

Madame, Monsieur,

Votre invité {guest.email} a accédé à la page de dépôt.

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Votre invité <a href="mailto:{guest.email}">{guest.email}</a> a accédé à la page de dépôt.
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
