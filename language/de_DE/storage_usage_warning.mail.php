<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Warnung Speicherbelegung

{alternative:plain}

Sehr geehrte Damen und Herren,

dies ist eine Warnung für die Speicherbelegung von {cfg:site_name} :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) hat nur noch size:warning.free_space} übrig ({warning.free_space_pct}%)
{endeach}

Weitere Details finden Sie unter {cfg:site_url}

Mit freundliche Grüßen
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    dies ist eine Warnung für die Speicherbelegung von {cfg:site_name} :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) hat nur noch {size:warning.free_space} übrig ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Weitere Details finden Sie unter <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Mit freundlichen Grüßen<br />
    {cfg:site_name}
</p>