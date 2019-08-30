<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Advarsel om lagerforbrug

{alternative:plain}

Kære administrator!

Lagerforbruget på {cfg:site_name} nærmer sig den øvre grænse:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) har kun {size:warning.free_space} tilbage ({warning.free_space_pct}%)
{endeach}

Du kan finde yderligere oplysninger på {cfg:site_url}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære administrator!
</p>

<p>
Lagerforbruget på {cfg:site_name} nærmer sig den øvre grænse:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) har kun {size:warning.free_space} tilbage ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Du kan finde yderligere oplysninger på <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>