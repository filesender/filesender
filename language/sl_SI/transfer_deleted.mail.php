<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Datoteka/e niso več na voljo za prenos

{alternative:plain}

Spoštovani,

Prenos n°{transfer.id} je bil izbrisan iz {cfg:site_name} s strani pošiljatelja ({transfer.user_email}) in ni več na voljo za prenos.

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    Prenos n°{transfer.id} je bil izbrisan iz <a href="{cfg:site_url}">{cfg:site_name}</a> s strani pošiljatelja (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) in ni več na voljo za prenos.
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>