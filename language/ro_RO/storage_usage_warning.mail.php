<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Avertisment privind utilizarea spațiului de stocare

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Utilizarea spațiului de stocare al serviciului {cfg:site_name} este în stare de avertizare: 

{each:warnings as warning} 
- {warning.filesystem} ({size:warning.total_space}) mai are doar {size:warning.free_space} disponibil ({warning.free_space_pct}%) 
{endeach}

Pentru detalii suplimentare accesați {cfg:site_url}

Cu respect, 
{cfg:site_name}

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Utilizarea spațiului de stocare al serviciului {cfg:site_name} este în stare de avertizare: 
</p>

<ul> {each:warnings as warning} 
<li>{warning.filesystem} ({size:warning.total_space}) mai are doar {size:warning.free_space} disponibil ({warning.free_space_pct}%)</li> {endeach} 
</ul>

<p> 
Pentru detalii suplimentare accesați <a href="{cfg:site_url}">{cfg:site_url}</a> </p>

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>