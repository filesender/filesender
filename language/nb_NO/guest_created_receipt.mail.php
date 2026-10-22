<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Gjesteinvitasjon sendt

{alternative:plain}

Hei,

En gjesteinvitasjon med tilgang til {cfg:site_name} er sendt til {guest.email}.

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    En gjesteinvitasjon med tilgang til <a href="{cfg:site_url}">{cfg:site_name}</a> er sendt til <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Med vennlig hilsen,<br />
    {cfg:site_name}
</p>
