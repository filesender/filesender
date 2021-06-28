<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (lembrete) Voucher de convidado recebido
subject: (lembrete) {guest.subject}

{alternative:plain}

Prezado(a) Senhor(a),

Esta mensagem é um lembrete. Veja abaixo um voucher que concede acesso a {cfg:site_name}. Você pode usar esse comprovante para enviar um conjunto de arquivos e disponibilizá-lo para download a um grupo de pessoas.

Emissor: {guest.user_email}
Link do Voucher: {guest.upload_link}

O voucher estará disponível até {date:guest.expires}, após o qual será automaticamente excluído.

{if:guest.message}Mensagem pessoal de {guest.user_email}: {guest.message}{endif}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado(a) Senhor(a),
</p>

<p>
    Esta mensagem é um lembrete. Veja abaixo um voucher que concede acesso a <a href="{cfg:site_url}">{cfg:site_name}</a>. Você pode usar esse comprovante para enviar um conjunto de arquivos e disponibilizá-lo para download a um grupo de pessoas.
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
            <td>{date:guest.expires}</td>
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
