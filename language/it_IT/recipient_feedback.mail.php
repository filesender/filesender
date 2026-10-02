<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Feedback dal tuo {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}ospite{endif} {target.email}

{alternative:plain}

Gentile utente,

Abbiamo ricevuto un feedback via email dal tuo {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}ospite{endif} {target.email}, lo trovi in allegato.

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Abbiamo ricevuto un feedback via email dal tuo {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}ospite{endif} {target.email}, lo trovi in allegato.
</p>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

