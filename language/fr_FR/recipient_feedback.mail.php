<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Retour de votre {if:target_type=="recipient"}destinataire{endif}{if:target_type=="guest"}invité{endif} {target.email}

{alternative:plain}

Madame, Monsieur,

Nous avons reçu un retour de votre {if:target_type=="recipient"}destinataire{endif}{if:target_type=="guest"}invité{endif} {target.email}, vous le trouverez attaché à ce message.

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Nous avons reçu un retour de votre {if:target_type=="recipient"}destinataire{endif}{if:target_type=="guest"}invité{endif} {target.email}, vous le trouverez attaché à ce message.
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
