<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Acesso de convidado a página de upload

{alternative:plain}

Prezado(a) Senhor(a),

O convidado {guest.email} acessou a página de upload do seu voucher.

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
    O convidado <a href="mailto:{guest.email}">{guest.email}</a> acessou a página de upload do seu voucher.
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
