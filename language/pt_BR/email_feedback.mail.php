<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Feedback do {if:target_type=="recipient"}destinatário{endif}{if:target_type=="guest"}convidado{endif}#{target_id} {target.email}

{alternative:plain}

Prezado(a) Senhor(a),

Nós recebemos um e-mail de Feedback do  {if:target_type=="recipient"}destinatário{endif}{if:target_type=="guest"}convidado{endif}#{target_id} {target.email}. Por favor, verifique o anexo.

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
    Nós recebemos um e-mail de Feedback do  {if:target_type=="recipient"}destinatário{endif}{if:target_type=="guest"}convidado{endif}#{target_id} {target.email}. Por favor, verifique o anexo.
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
