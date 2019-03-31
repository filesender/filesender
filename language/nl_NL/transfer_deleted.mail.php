<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Bestand(en) niet langer beschikbaar om te downloaden

{alternative:plain}

Geachte heer, mevrouw,

De transfer n°{transfer.id} is verwijderd van {cfg:site_name} door de verzender ({transfer.user_email}) en is niet langer beschikbaar om te downloaden.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
    De transfer n°{transfer.id} is verwijderd van <a href="{cfg:site_url}">{cfg:site_name}</a> door de verzender (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) en is niet langer beschikbaar om te downloaden.
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>