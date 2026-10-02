<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Tiedosto{if:transfer.files>1}ja{endif} noudettavissa

{alternative:plain}

Hei!

Käyttäjä {transfer.user_email} on jakanut palveluun {cfg:site_name} yhden tai useampia tiedostoja ja merkinnyt sinut vastaanottajaksi:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Latauslinkki: {recipient.download_link}

Tiedostojako on saatavilla {date:transfer.expires} asti, minkä jälkeen tiedostot poistetaan palvelusta automaattisesti. Muista siis noutaa tiedostot ajoissa!

{if:transfer.message || transfer.subject}
Henkilökohtainen viesti lähettäjältä {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Käyttäjä {transfer.user_email} on jakanut palveluun {cfg:site_name} yhden tai useampia tiedostoja ja merkinnyt sinut vastaanottajaksi:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Tiedostojaon tiedot</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Tiedosto{if:transfer.files>1}t{endif}</td>
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
            <td>Koko</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Erääntyy</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Latauslinkki</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Henkilökohtainen viesti lähettäjältä {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>