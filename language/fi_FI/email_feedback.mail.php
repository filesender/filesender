<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Palaute lähettäjältä {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}

{alternative:plain}

Hei!

Olemme vastaanottaneet oheisen palautteen lähettäjältä {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}, ks. liite.

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Olemme vastaanottaneet oheisen palautteen lähettäjältä {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}, ks. liite.
</p>

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>

