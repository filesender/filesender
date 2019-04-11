<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Opozorilo o porabi prostora

{alternative:plain}

Spoštovani,

Poraba prostora aplikacije {cfg:site_name} opozarja :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) ima le še {size:warning.free_space} prostega prostora ({warning.free_space_pct}%)
{endeach}

Podrobnosti lahko najdete na {cfg:site_url}

Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    Poraba prostora aplikacije {cfg:site_name} is warning :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) ima le še {size:warning.free_space} prostega prostora ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Podrobnosti lahko najdete na <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>