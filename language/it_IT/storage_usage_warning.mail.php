<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Avviso sull'utilizzo dello storage

{alternative:plain}

Gentile utente,

L'utilizzo dello storage di {cfg:site_name} è in allarme :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) ha solamente {size:warning.free_space} libero ({warning.free_space_pct}%)
{endeach}

Puoi trovare ulteriori dettagli su {cfg:site_url}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    L'utilizzo dello storage di {cfg:site_name} è in allarme :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) ha solamente {size:warning.free_space} libero ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Puoi trovare ulteriori dettagli su <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

