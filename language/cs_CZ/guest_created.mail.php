<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Pozvánka pro hosta přijata
subject: {guest.subject}

{alternative:plain}

Vážený uživateli,

Níže naleznete pozvánku, která Vám umožní přístup k {cfg:site_name}. Můžete ji použít k nahrání souboru/souborů, které budou přístupné skupině uživatelů. 

Odesílatel: {guest.user_email}
Odkaz na pozvánku: {guest.upload_link}

{if:guest.does_not_expire}
Tato pozvánka neexpiruje.
{else}
Pozvánka je platná do {date:guest.expires}, poté bude automaticky vymazána.
{endif}

{if:guest.message}Zpráva od {guest.user_email}: {guest.message}{endif}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Níže naleznete pozvánku, která Vám umožní přístup k <a href="{cfg:site_url}">{cfg:site_name}</a>. Můžete ji použít k nahrání souboru/souborů, které budou přístupné skupině uživatelů.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Podrobnosti pozvánky</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Odesílatel</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>Odkaz na pozvánku</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>Platná do</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    Zpráva od {guest.user_email}:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>

