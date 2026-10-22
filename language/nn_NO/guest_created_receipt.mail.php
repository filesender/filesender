<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gjesteinvitasjon send

{alternative:plain}

Hei,

Ei gjesteinvitasjon med tilgang til {cfg:site_name} er send til {guest.email}.

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Ei gjesteinvitasjon med tilgang til <a href="{cfg:site_url}">{cfg:site_name}</a> er send til <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
