<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Alerte du stockage

{alternative:plain}

Madame, Monsieur,

Le stockage de {cfg:site_name} est au niveau bas :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) dispose de seulement {size:warning.free_space} restants ({warning.free_space_pct}%)
{endeach}

Vous pourrez trouver plus de détails sur {cfg:site_url}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Le stockage de {cfg:site_name} est au niveau bas :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) dispose de seulement {size:warning.free_space} restants ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Vous pourrez trouver plus de détails sur <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
