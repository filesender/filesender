<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Файли більше недоступні для завантаження

{alternative:plain}

Шановні панове,

Пересилання №{transfer.id} було видалено з {cfg:site_name} відправником ({transfer.user_email}) і більше не доступне для завантаження.

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Пересилання №{transfer.id} було видалено з <a href="{cfg:site_url}">{cfg:site_name}</a> відправником (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) і більше не доступне для завантаження.
</p>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
