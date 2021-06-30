<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Lembretes automáticos enviados para envio de arquivos n° {transfer.id}
subject: (lembretes automáticos enviados) {transfer.subject}

{alternative:plain}

Prezado Senhor(a),

Um lembrete automático foi enviado para os destinatários que não fizeram o download dos arquivos da sua transferência n° {transfer.id} em {cfg:site_name} ({transfer.link}):

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado Senhor(a),
</p>

<p>
    Um lembrete automático foi enviado para os destinatários que não fizeram o download dos arquivos da sua <a href="{transfer.link}">transferência n° {transfer.id}</a> em <a href="{cfg:site_url}">{cfg:site_name}</a>:
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
