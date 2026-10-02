<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Upozorenje o upotrebi diskovnog prostora

{alternative:plain}

Poštovani,

Diskovni prostor za {cfg:site_name}, upozorenje :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) ima {size:warning.free_space} slobodno ({warning.free_space_pct}%)
{endeach}

Više detalja možete pronaći na {cfg:site_url}

Lijepi pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Poštovani,
</p>

<p>
    Diskovni prostor za {cfg:site_name}, upozorenje :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) ima {size:warning.free_space} slobodno ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Više detalja možete pronaći na <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Lijepi pozdrav,<br />
    {cfg:site_name}
</p>