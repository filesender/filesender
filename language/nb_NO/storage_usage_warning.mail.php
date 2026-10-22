<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Varsel om lagringsbruk

{alternative:plain}

Hei,

Lagringsbruken til {cfg:site_name} krever oppmerksomhet:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) har bare {size:warning.free_space} igjen ({warning.free_space_pct}%)
{endeach}

Du finner flere detaljer på {cfg:site_url}

Med vennlig hilsen,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Lagringsbruken til {cfg:site_name} krever oppmerksomhet:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) har bare {size:warning.free_space} igjen ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Du finner flere detaljer på <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Med vennlig hilsen,<br />
    {cfg:site_name}
</p>
