<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Ostrzeżenie o wykorzystaniu przestrzeni przechowywania

{alternative:plain}

Szanowni Państwo,

Ostrzeżenia dotyczące wykorzystania przestrzeni przechowywania danych w serwisie {cfg:site_name}:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) posiada tylko  {size:warning.free_space} wolnego ({warning.free_space_pct}%)
{endeach}

Więcej szczegółów dostępnych pod adresem {cfg:site_url}

Z Poważaniem,
{cfg:site_name}

{alternative:html}

<p>
    Szanowni Państwo,
</p>

<p>
    Ostrzeżenia dotyczące wykorzystania przestrzeni przechowywania danych w serwisie {cfg:site_name}:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) posiada tylko {size:warning.free_space} wolnego ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Więcej szczegółów dostępnych pod adresem <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Z Poważaniem,<br />
    {cfg:site_name}
</p>
