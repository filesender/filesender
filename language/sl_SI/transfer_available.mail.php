<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: datotek{if:transfer.files>1}e so pripravljene{else}a je pripravljena{endif} za prenos
subject: {transfer.subject}

{alternative:plain}

Spoštovani,
Naslednj{if:transfer.files>1}e datoteke so bile naložene{else}a je bila naložena{endif} na {cfg:site_name} s strani {transfer.user_email}. Dodeljena Vam je bila pravica za prenos {if:transfer.files>1}njihove vsebine{else}njene vsebine{endif} :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Povezava za prenos: {recipient.download_link}
Prenos je na voljo do {date:transfer.expires}. Po tem datumu bo prenos izbrisan.

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
    Naslednj{if:transfer.files>1}e datoteke so bile naložene{else}a je bila naložena{endif} na <a href="{cfg:site_url}">{cfg:site_name}</a> s strani <a href="mailto:{transfer.user_email}">{transfer.user_email}</a>. Dodeljena Vam je bila pravica za prenos {if:transfer.files>1}njihove vsebine{else}njene vsebine{endif}.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Podrobnosti prenosa</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Datotek{if:transfer.files>1}e{else}a{endif}</td>
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
            <td>Povezava za prenos</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Osebno sporočilo od {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Lep pozdrav,<br />
    {cfg:site_name}
</p>