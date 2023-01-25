<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Voucher de convidado recebido
subject: {guest.subject}

{alternative:plain}

Prezado(a),

Você recebeu um voucher para acessar o {cfg:site_name}. Com essa permissão, você pode carregar um arquivo, uma única vez, e disponibilizá-lo para outras pessoas.

Esse serviço permite que você compartilhe um arquivo grande, sem sobrecarregar o limite de armazenamento da caixa postal do destinatário. Além disso, ele pode escolher o melhor momento e local para baixar o arquivo, dentro do período de armazenamento definido para o serviço.

Emissor: {guest.user_email}
Link do Voucher: {guest.upload_link}

O voucher estará disponível até {date:guest.expires}, após o qual será automaticamente excluído.

{if:guest.message}Mensagem pessoal de {guest.user_email}: {guest.message}{endif}

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
    Você recebeu um voucher para acessar o {cfg:site_name}. Com essa permissão, você pode carregar um arquivo, uma única vez, e disponibilizá-lo para outras pessoas.
</p>
<p>
    Esse serviço permite que você compartilhe um arquivo grande, sem sobrecarregar o limite de armazenamento da caixa postal do destinatário. Além disso, ele pode escolher o melhor momento e local para baixar o arquivo, dentro do período de armazenamento definido para o serviço.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalhes do Voucher</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Emissor</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Link do Voucher</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Válido até</td>
{if:guest.does_not_expire}
            <td>Nunca</td>
{else}
            <td>{date:guest.expires}</td>
{endif}
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Mensagem pessoal de {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
