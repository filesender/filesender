<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Asunto: Alerta uso de almacenamiento

{alternative:plain}

Hola,

El uso del almacenamiento del servicio FileSender de {cfg:site_name} tiene una alerta:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) solo tiene libre {size:warning.free_space} ({warning.free_space_pct}%)
{endeach}

Puedes encontrar más información en {cfg:site_url}

Saludos,
{cfg:site_name}

{alternative:html}

<p>
    Hola,
</p>

<p>
    El uso del almacenamiento del servicio FileSender de {cfg:site_name} tiene una alerta:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) solo tiene libre {size:warning.free_space} ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Puedes encontrar más información en <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Saludos,<br />
    {cfg:site_name}
</p>