<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Convidado começou a fazer upload de arquivos

{alternative:plain}

Prezado(a) Senhor(a),

O convidado a seguir começou a fazer upload de arquivos do seu voucher:

Convidado: {guest.email}
Link do Voucher: {cfg:site_url}?s=upload&vid={guest.token}

O voucher estará disponível até {date:guest.expires}. Após esse tempo, ele será automaticamente excluído.

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado(a) Senhor(a),
</p>

<p>
    O convidado a seguir começou a fazer upload de arquivos do seu voucher:
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
