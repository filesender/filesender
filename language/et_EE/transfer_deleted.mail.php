<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Fail(id) ei ole enam saadaval
subject: (failid ei ole enam saadaval) {transfer.subject}

{alternative:plain}

Tere,

Failijagamine ID-ga {transfer.id} on kustutatud {cfg:site_name} veebisaidist kasutaja ({transfer.user_email}) poolt.

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    Failijagamine ID-ga {transfer.id} on kustutatud <a href="{cfg:site_url}">{cfg:site_name}</a> veebsaidist kasutaja (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) poolt.
</p>

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
