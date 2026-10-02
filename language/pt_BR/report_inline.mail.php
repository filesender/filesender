<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Relatório sobre {if:target.type == "File"}o arquivo{elseif:target.type == "Recipient"}o destinatário{else}a transferência{endif} nº {target.id}

{alternative:plain}

Prezado Senhor(a),

Aqui está o relatório sobre {if:target.type == "File"}o arquivo{elseif:target.type == "Recipient"}o destinatário{else}a transferência{endif}:

{if:target.type == "File"}Arquivo{elseif:target.type == "Recipient"}Destinatário{else}Transferência{endif} nº {target.id}

{if:target.type == "Transfer"}
Esta transferência tem {transfer.files} arquivos com um tamanho total de {size:transfer.size}.
    
Esta transferência está/ficou disponível até {date:transfer.expires}.

Esta transferência foi enviada para {transfer.recipients}.
{endif}
{if:target.type == "File"}
Este arquivo tem o nome {file.path}, tem o tamanho de {size:file.size} e está/estava disponível até {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Este destinatário tem o endereço de email {recipient.email} e expirará/expirou em {date:recipient.expires}.
{endif}

Aqui está o registro completo do que aconteceu com a transferência:

{raw:content.plain}

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
    Aqui está o relatório sobre {if:target.type == "File"}o arquivo{elseif:target.type == "Recipient"}o destinatário{else}a transferência{endif}:<br /><br />
    
    {if:target.type == "File"}Arquivo{elseif:target.type == "Recipient"}Destinatário{else}Transferência{endif} nº {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Esta transferência tem {transfer.files} arquivos com um tamanho total de {size:transfer.size}.<br /><br />
    
    Esta transferência está/ficou disponível até {date:transfer.expires}.<br /><br />
    
    Esta transferência foi enviada para {transfer.recipients}.
    {endif}
    {if:target.type == "File"}
    Este arquivo tem o nome {file.path}, tem o tamanho de {size:file.size} e está/estava disponível até {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Este destinatário tem o endereço de email {recipient.email} e expirará/expirou em {date:recipient.expires}.
    {endif}
</p>

<p>
    Aqui está o registro completo do que aconteceu com a transferência:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Data</th>
            <th>Evento</th>
            <th>Endereço de IP</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Atenciosamente,<br/>
{cfg:site_name}</p>
