<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Opinión del {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}invitado{endif} {target.email}

{alternative:plain}

Hola,

Hemos recibido un comentario del {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}invitado{endif} {target.email}. Lo puedes encontrar adjunto.

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    hemos recibido un comentario del {if:target_type=="recipient"}destinatario{endif}{if:target_type=="guest"}invitado{endif} {target.email}. Lo puedes encontrar adjunto.
</p>

<p>
    Saludos,<br />
    {cfg:site_name}
</p>