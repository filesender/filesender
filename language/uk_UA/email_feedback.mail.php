<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Зворотний зв'язок від {if:target_type=="recipient"}отримувача{endif}{if:target_type=="guest"}гостя{endif} #{target_id} {target.email}

{alternative:plain}

Шановні панове,

Ми отримали повідомлення від {if:target_type=="recipient"}отримувача{endif}{if:target_type=="guest"}гостя{endif} #{target_id} {target.email}, воно додане до цього листа.

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Ми отримали повідомлення від {if:target_type=="recipient"}отримувача{endif}{if:target_type=="guest"}гостя{endif} #{target_id} {target.email}, воно додане до цього листа.
</p>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
