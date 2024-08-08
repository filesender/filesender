<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Soubor{if:transfer.files>1}y{endif} ke stažení
subject: {transfer.subject}

{alternative:plain}

Vážený uživateli,

Následující {if:transfer.files>1}soubory byly nahrány{else}soubor byl nahrán{endif} na {cfg:site_name} uživatelem {transfer.user_email} a Vám bylo uděleno oprávnění ke stažení {if:transfer.files>1}jejich{else}jeho{endif} obsahu:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().name} ({size:transfer.files.first().size})
{endif}

Odkaz ke stažení: {recipient.download_link}

Přenos je dostupný do {date:transfer.expires}, poté bude automaticky odstraněn.

{if:transfer.message || transfer.subject}
Zpráva od {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Následující {if:transfer.files>1}soubory byly nahrány{else}soubor byl nahrán{endif} na <a href="{cfg:site_url}">{cfg:site_name}</a> uživatelem <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> a Vám bylo uděleno oprávnění ke stažení {if:transfer.files>1}jejich{else}jeho{endif} obsahu.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detaily</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Soubor{if:transfer.files>1}y{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().name} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        {if:transfer.files>1}
        <tr>
            <td>Velikost</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Expirace</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Odkaz ke stažení</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Zpráva od {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>

