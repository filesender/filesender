<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (opomnik) {if:transfer.files>1}Datoteke, pripravljene{else}Datoteka, pripravljena{endif} za prenos
subject: (opomnik) {transfer.subject}

{alternative:plain}

Spoštovani,

prejeli ste opomnik, da {if:transfer.files>1}so bile naložene datoteke{else}je bila naložena datoteka{endif} na {cfg:site_name} s strani {transfer.user_email}, Vam pa so bile dodeljene pravice za prenos {if:transfer.files>1}njihovih vsebin{else}njene vsebine{endif} :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Povezava za prenos: {recipient.download_link}

Prenos je na voljo do {date:transfer.expires}. Po tem datumu bo avtomatsko izbrisan.

{if:transfer.message || transfer.subject}
Osebno sporočilo od {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}
Lep pozdrav,
{cfg:site_name}

{alternative:html}

<p>
    Spoštovani,
</p>

<p>
    prejeli ste opomnik, da {if:transfer.files>1}so bile naložene datoteke{else}je bila naložena datoteka{endif} na {cfg:site_name} s strani {transfer.user_email}, Vam pa so bile dodeljene pravice za prenos {if:transfer.files>1}njihovih vsebin{else}njene vsebine{endif}.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Podrobnosti prenosa</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{if:transfer.files>1}Datoteke{else}Datoteka{endif}</td>
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
            <td>Velikost prenosa</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Rok veljavnosti</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Povezava do prenosa</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Osebno sporočilo od {transfer.user_email}:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>