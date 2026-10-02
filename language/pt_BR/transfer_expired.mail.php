<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Arquivos não mais disponíveis{else}Arquivo não mais disponível{endif} para download

{alternative:plain}

Prezado Senhor(a),

O envio do arquivo n° {transfer.id} expirou e não está mais disponível para download.

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
    O envio do arquivo n° {transfer.id} expirou e não está mais disponível para download.
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
