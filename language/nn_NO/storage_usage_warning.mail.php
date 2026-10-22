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

Lagringsbruken til {cfg:site_name} krev merksemd:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) har berre {size:warning.free_space} att ({warning.free_space_pct}%)
{endeach}

Du finn fleire detaljar på {cfg:site_url}

Med venleg helsing,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Lagringsbruken til {cfg:site_name} krev merksemd:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) har berre {size:warning.free_space} att ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Du finn fleire detaljar på <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Med venleg helsing,<br />
    {cfg:site_name}
</p>
