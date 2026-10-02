<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {files.first().transfer.subject}

{alternative:plain}

Prezado(a),

{if:files>1}Os seguintes arquivos foram baixados{else}O seguinte arquivo foi baixado{endif} do {cfg:site_name} por {recipient.email}:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Você pode acessar seus arquivos e visualizar estatísticas detalhadas de download na página de transferências em {files.first().transfer.link}.

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
    {if:files>1}Os seguintes arquivos foram baixados{else}O seguinte arquivo foi baixado{endif} do {cfg:site_name} por {recipient.email}:
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    Você pode acessar seus arquivos e visualizar estatísticas detalhadas de download na página de transferências em <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
