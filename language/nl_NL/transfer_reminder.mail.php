onderwerp: (herinnering) Bestand{if:transfer.files>1}en{endif} beschikbaar voor download
onderwerp: (herinnering) {transfer.subject}

{alternative:plain}

Geachte mevrouw, heer,

Dit is een herinnering, de volgende {if:transfer.files>1}bestanden zijn{else}bestand is{endif} geüpload naar <a href="{cfg:site_url}">{cfg:site_name}</a> door <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> en je hebt toestemming gekregen om {if:transfer.files>1}ze{else}het{endif} te downloaden :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Download link: {recipient.download_link}

De transactie is beschikbaar tot {date:transfer.expires} na die tijd wordt het automatisch verwijderd.

{if:transfer.message || transfer.subject}
Persoonlijk bericht van {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer,
</p>

<p>
   Dit is een herinnering, de volgende {if:transfer.files>1}bestanden zijn{else}bestand is{endif} geüpload naar <a href="{cfg:site_url}">{cfg:site_name}</a> door <a href="{cfg:site_url}">{cfg:site_name}</a> en je hebt toestemming gekregen om {if:transfer.files>1}ze{else}het{endif} te downloaden :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Transaction details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>File{if:transfer.files>1}s{endif}</td>
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
            <td>Transfer grootte</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Vervaldatum</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Download link</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Persoonlijk bericht van {transfer.user_email}:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>