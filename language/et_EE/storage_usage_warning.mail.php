<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Salvestusruumi probleem

{alternative:plain}

Tere,

Salvestusruumi probleem veebisaidis {cfg:site_name} :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) only has {size:warning.free_space} left ({warning.free_space_pct}%)
{endeach}

Lugupidamisega,
{cfg:site_name}

{alternative:html}

<p>
    Tere,
</p>

<p>
    Salvestusruumi probleem veebisaidis {cfg:site_name} :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) only has {size:warning.free_space} left ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Lugupidamisega,<br />
    {cfg:site_name}
</p>
