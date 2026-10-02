<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Автоматичні нагадування надіслано для пересилання №{transfer.id}

{alternative:plain}

Шановні панове,

Автоматичне нагадування було надіслано отримувачам, які не завантажили файли з вашого пересилання №{transfer.id} на {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Автоматичне нагадування було надіслано отримувачам, які не завантажили файли з вашого <a href="{transfer.link}">пересилання №{transfer.id}</a> на <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
