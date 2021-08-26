<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Arquivos removidos{else}Arquivo removido{endif}
subject: ({if:transfer.files>1}arquivos removidos{else}arquivo removido{endif}) {transfer.subject}

{alternative:plain}

Prezado Senhor(a),

Sua transferência n° {transfer.id} foi excluída de {cfg:site_name} e não está mais disponível para download ({transfer.link}).

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
    Sua <a href="{transfer.link}">transferência n° {transfer.id}</a> foi excluída de <a href="{cfg:site_url}">{cfg:site_name}</a> e não está mais disponível para download.
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>