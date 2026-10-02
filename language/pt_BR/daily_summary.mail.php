<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Resumo diário de transferências

{alternative:plain}

Prezado(a) Senhor(a),

Veja abaixo um resumo dos downloads para sua transferência {transfer.id} (carregado em {date:transfer.created}) :

{if:events}
{each:events as event}
  - Destinatário {event.who} fez download do  {if:event.what == "archive"}arquivo{else}arquivo {event.what_name}{endif} em {datetime:event.when}
{endeach}
{else}
Sem downloads
{endif}

Você pode encontrar mais detalhes em {transfer.link}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<div class="header-icons-filesender">
	<p>
		<img src="{cfg:site_url}/images/banner-996px.png" alt="RNP - FileSender" title="RNP - FileSender" draggable="false" style="margin: auto; height: 90px;">
	</p>
</div>


<p>
    Caro Sr ou Sra,
</p>

<p>
  Por favor, encontre abaixo um resumo dos downloads para sua transferência {transfer.id} (uploaded {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Recipiente {event.who} fez o download do {if:event.what == "archive"}arquivo{else}arquivo{event.what_name}{endif} em {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Sem downloads
</p>
{endif}

<p>
 Você pode encontrar mais detalhes em <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>