Ondewerp: Bestand{if:transfer.files>1}en{endif} succesvol geüpload

{alternative:plain}

Geachte mevrouw, heer, 

De volgende {if:transfer.files>1}bestanden zijn{else}bestand is{endif} succesvol geüpload naar {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Meer informatie: {transfer.link}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer, 
</p>

<p>
    De volgende {if:transfer.files>1}bestanden zijn{else}bestand is{endif} succesvol geüpload naar <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Transaction details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Bestand{if:transfer.files>1}en{endif}</td>
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
        <tr>
            <td>Grootte</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Meer informatie</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>

