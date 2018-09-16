subject: Soubor{if:transfer.files>1}y{endif} - úspěšně nahráno

{alternative:plain}

Vážený uživateli,

Následující {if:transfer.files>1}soubory byly nahrány{else}soubor byl nahrán{endif} na {cfg:site_name}.

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().name} ({size:transfer.files.first().size})
{endif}

Více informací: {transfer.link}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Následující  {if:transfer.files>1}soubory byly nahrány{else}soubor byl nahrán{endif} na <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detaily transakce</th>
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
        <tr>
            <td>Velikost</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Více informací</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>

