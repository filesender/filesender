<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Falha ao entregar mensagem 

{alternative:plain}

 Prezado(a) Senhor(a),

A entrega de mensagens falhou para um ou mais destinatários:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transferência #{bounce.target.transfer.id} destinatário {bounce.target.email} em {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Guest {bounce.target.email} em {datetime:bounce.date}
{endif}
{endeach}

Você pode ver mais detalhes em {cfg:site_url}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<div class="header-icons-filesender">
	<p>
		<img src="{cfg:site_url}/images/banner-996px.png" alt="RNP - FileSender" title="RNP - FileSender" draggable="false" style="margin: auto; height: 90px;">
	</p>
</div>

<p>
    Prezado(a) Senhor(a),
</p>

<p>
    A entrega de mensagens falhou para um ou mais destinatários:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Transferência #{bounce.target.transfer.id}</a> destinatário {bounce.target.email} on {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Convidado {bounce.target.email} on {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Você pode ver mais detalhes em <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
   Atenciosamente,<br />
    {cfg:site_name}
</p>
