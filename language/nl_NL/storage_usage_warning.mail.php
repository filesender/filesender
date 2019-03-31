<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Waarschuwing opslaggebruik

{alternative:plain}

Geachte heer, mevrouw,

Het opslaggebruik van {cfg: site_name} waarschuwt:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) heeft alleen  {size:warning.free_space} nog over ({warning.free_space_pct}%)
{endeach}

U kunt aanvullende informatie vinden op {cfg:site_url}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Geachte heer, mevrouw,
</p>

<p>
    Het opslaggebruik van {cfg: site_name} waarschuwt:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) heeft alleen {size:warning.free_space} nog over ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    U kunt aanvullende informatie vinden op <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>