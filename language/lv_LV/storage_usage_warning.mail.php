<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Brīdinājums par krātuves izmantošanu

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Krātuves {cfg:site_name} lietojums brīdina :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) tikai {size:warning.free_space} atlicis ({warning.free_space_pct}%)
{endeach}

Papildinformāciju varat atrast {cfg:site_url}

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Krātuves {cfg:site_name} lietojums brīdina :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) tikai {size:warning.free_space} atlicis ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Papildinformāciju varat atrast <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Ar cieņu <br />
    {cfg:site_name}
</p>