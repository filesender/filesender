<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Invitation revoquée

{alternative:plain}

Madame, Monsieur,

Une invitation de {guest.user_email} a été revoquée.

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Une invitation de <a href="mailto:{guest.user_email}">{guest.user_email}</a> a été revoquée.
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
