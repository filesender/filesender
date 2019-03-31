<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Promemoria automatici inviati per l'invio del file n°{transfer.id}

{alternative:plain}

Gentile utente,

Un promemoria automatico è stato inviato ai destinatari che non hanno scaricato i file dal tuo invio n°{transfer.id} su {cfg:site_name} ({transfer.link}):

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Cordiali saluti,
{cfg:site_name}

{alternative:html}

<p>
    Gentile utente,
</p>

<p>
    Un promemoria automatico è stato inviato ai destinatari che non hanno scaricato i file dal tuo <a href="{transfer.link}">invio n°{transfer.id}</a> su <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Cordiali saluti,<br />
    {cfg:site_name}
</p>

