<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Обратная связь от {if:target_type=="recipient"}получателя{endif}{if:target_type=="guest"}гостя{endif}#{target_id} {target.email}

{alternative:plain}

Товарищ!

Мы получили обратную связь от {if:target_type=="recipient"}получателя{endif}{if:target_type=="guest"}гостя{endif}#{target_id} {target.email}


Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Мы получили обратную связь от {if:target_type=="recipient"}получателя{endif}{if:target_type=="guest"}гостя{endif}#{target_id} {target.email}
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
