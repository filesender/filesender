<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Доставка не произведена

{alternative:plain}

    Дорогой товарищ!

Один или несколько твоих получателей не смогли получить твое сообщение:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Передача #{bounce.target.transfer.id} получатель {bounce.target.email} в {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Гость {bounce.target.email} в {datetime:bounce.date}
{endif}
{endeach}

Подробные сведения можно найти на {cfg:site_url}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Дорогой товарищ!
</p>

<p>
Один или несколько твоих получателей не смогли получить твое сообщение:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Передача #{bounce.target.transfer.id}</a> получатель {bounce.target.email} в {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Гость {bounce.target.email} в {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Подробные сведения можно найти на <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>