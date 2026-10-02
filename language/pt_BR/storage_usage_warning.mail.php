<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Aviso de uso de armazenamento

{alternative:plain}

Prezado Senhor(a),

O uso de armazenamento do {cfg: site_name} está avisando:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) tem apenas {size:warning.free_space} de ({warning.free_space_pct}%)
{endeach}

Você pode encontrar detalhes adicionais em {cfg:site_url}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<div class="header-icons-filesender">
	<p>
		<img src="{cfg:site_url}/images/banner-996px.png" alt="RNP - FileSender" title="RNP - FileSender" draggable="false" style="margin: auto; height: 90px;">
	</p>
</div>


<p>
    Prezado Senhor(a),
</p>

<p>
    O uso de armazenamento do {cfg: site_name} está avisando:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) tem apenas {size:warning.free_space} de ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Você pode encontrar detalhes adicionais em <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
