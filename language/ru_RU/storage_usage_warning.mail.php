<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Предупреждение о состоянии хранилища данных

{alternative:plain}

Товарищ!

Хранилище данных {cfg:site_name} рапортует о своем состоянии:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) имеет всего свободного пространства {size:warning.free_space} ({warning.free_space_pct}%)
{endeach}

Подробности можно найти здесь: {cfg:site_url}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Хранилище данных {cfg:site_name} рапортует о своем состоянии:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) имеет всего свободного пространства {size:warning.free_space} ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Подробности можно найти здесь: <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
