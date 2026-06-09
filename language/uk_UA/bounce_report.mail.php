<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Помилка доставки повідомлення

{alternative:plain}

Шановні панове,

Один або кілька ваших отримувачів не змогли отримати ваше повідомлення(ня):

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Пересилання #{bounce.target.transfer.id}, отримувач {bounce.target.email} від {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Гість {bounce.target.email} від {datetime:bounce.date}
{endif}
{endeach}

Додаткову інформацію можна знайти за посиланням {cfg:site_url}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Один або кілька ваших отримувачів не змогли отримати ваше повідомлення(ня):
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Пересилання #{bounce.target.transfer.id}</a>, отримувач {bounce.target.email} від {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Гість {bounce.target.email} від {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Додаткову інформацію можна знайти за посиланням <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
