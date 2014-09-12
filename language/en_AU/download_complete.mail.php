subject: {cfg:site_name}: Download Complete

{alternative:plain}

Dear Sir or Madam,

Your download consisting of the following file has finished.

Files:
{fileinfo}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    Your download consisting of the following file has finished.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">File details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>File</td>
            <td>
                {filename}
            </td>
        </tr>
        <tr>
            <td>Size</td>
            <td>{filesize}</td>
        </tr>
        <tr>
            <td>Download link</td>
            <td><a href="{cfg:site_url}?s=download&token={recipient.token}">{cfg:site_url}?s=download&amp;token={recipient.token}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
