<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Levytilahälytys

{alternative:plain}

Hei!

Palvelun {cfg:site_name} vapaa levytila saattaa olla vähissä:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) vapaata {size:warning.free_space} jäljellä ({warning.free_space_pct}%)
{endeach}

Lisätietoja osoitteessa {cfg:site_url}

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Palvelun {cfg:site_name} vapaa levytila saattaa olla vähissä:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) vapaata {size:warning.free_space} jäljellä ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Lisätietoja osoitteessa <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>

