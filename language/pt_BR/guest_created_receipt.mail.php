<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Voucher de convidado enviado

{alternative:plain}

Prezado(a) Senhor(a),

Um voucher concedendo acesso a {cfg:site_name} foi enviado para {guest.email}.

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado(a) Senhor(a),
</p>

<p>
    Um voucher concedendo acesso a <a href="{cfg:site_url}">{cfg:site_name}</a> foi enviado para <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
