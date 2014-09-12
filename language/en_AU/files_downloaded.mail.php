subject: {cfg:site_name}: Download receipt

{alternative:plain}

Dear Sir or Madam,

One or more of your uploaded files have been downloaded from {cfg:site_name} by {downloadedfrom}. 
You can access your files and view detailed download statistics on the My Files page at {cfg:site_url}?s=files.


Files:
{fileinfo}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    One or more of your uploaded files have been downloaded from {cfg:site_name} by {downloadedfrom}. <br/>
    You can access your files and view detailed download statistics on the My Files page at {cfg:site_url}?s=transfers.
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
    </tbody>
</table>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
