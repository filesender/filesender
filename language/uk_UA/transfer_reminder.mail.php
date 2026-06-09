<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (нагадування) Файл{if:transfer.files>1}и{endif} доступні для завантаження
subject: (нагадування) {transfer.subject}

{alternative:plain}

Шановні панове,

Це нагадування: наступні {if:transfer.files>1}файли були{else}файл був{endif} завантажені на {cfg:site_name} користувачем {transfer.user_email}, і вам надано дозвіл на завантаження {if:transfer.files>1}їхнього{else}його{endif} вмісту :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Посилання на завантаження: {recipient.download_link}

Пересилання доступне до {date:transfer.expires}, після чого воно буде автоматично видалено.

{if:transfer.message || transfer.subject}
Особисте повідомлення від {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

З повагою,
{cfg:site_name}

{alternative:html}

<p>
    Шановні панове,
</p>

<p>
    Це нагадування: наступні {if:transfer.files>1}файли були{else}файл був{endif} завантажені на <a href="{cfg:site_url}">{cfg:site_name}</a> користувачем <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>, і вам надано дозвіл на завантаження {if:transfer.files>1}їхнього{else}його{endif} вмісту.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Деталі операції</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Файл{if:transfer.files>1}и{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        {if:transfer.files>1}
        <tr>
            <td>Загальний обсяг</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Дата закінчення дії</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Посилання на завантаження</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Особисте повідомлення від {transfer.user_email}:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    З повагою,<br />
    {cfg:site_name}
</p>
