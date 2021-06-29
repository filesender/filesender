<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Voucher de convidado expirado

{alternative:plain}

Prezado(a) Senhor(a),

Um voucher de convidado do {guest.user_email} expirou.

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado(a) Senhor(a),
</p>

<p>
    Um voucher de convidado do <a href="mailto:{guest.user_email}">{guest.user_email}</a> expirou.
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
