<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: O convidado finalizou o upload de arquivos

{alternative:plain}

Prezado(a) Senhor(a),

O convidado a seguir terminou o upload de arquivos usando um voucher de convidado:

Convidado: {guest.email}
Link do Voucher: {cfg:site_url}?s=upload&vid={guest.token}

O voucher estará disponível até {date:guest.expires}, após o qual será automaticamente excluído.

Atenciosamente,
{cfg:site_name}

{alternative:html}

<div class="header-icons-filesender">
	<p>
		<img src="{cfg:site_url}images/banner-996px.png" alt="RNP - FileSender" title="RNP - FileSender" draggable="false" style="margin: auto; height: 90px;">
	</p>
</div>

<p>
    Prezado(a) Senhor(a),
</p>

<p>
O convidado a seguir terminou o upload de arquivos usando um voucher de convidado:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalhes do Voucher</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Convidado</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>Link do Voucher</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>Válido até</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>

