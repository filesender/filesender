<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Попередження про використання сховища

{alternative:plain}

Шановні панове,

Використання сховища {cfg:site_name} перебуває на критичному рівні :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) залишилося лише {size:warning.free_space} ({warning.free_space_pct}%)
{endeach}

Додаткову інформацію можна знайти за посиланням {cfg:site_url}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Використання сховища {cfg:site_name} перебуває на критичному рівні :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) залишилося лише {size:warning.free_space} ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Додаткову інформацію можна знайти за посиланням <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
