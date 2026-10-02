<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Fișierul (fișierele) nu mai poate (pot) fi descărcat(e)

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Transferul nr. {transfer.id} a fost șters de pe {cfg:site_name} de către expeditorul ({transfer.user_email}) și nu mai este disponibil pentru descărcare.

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Transferul nr. {transfer.id} a fost șters de pe <a href="{cfg:site_url}">{cfg:site_name}</a> de către expeditorul (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) și nu mai este disponibil pentru descărcare. 
</p>

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>