subject: {cfg:site_name}: File{if:transfer.files>1}s{endif} successfully uploaded

{alternative:plain}

Dear Sir or Madam,

The following {if:transfer.files>1}files have{else}file has{endif} been successfully uploaded to {cfg:site_name}.

{text_file_list}

Your transfers list: {cfg:site_url}?s=transfers

Files:
{fileinfo}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    The following {if:transfer.files>1}files have{else}file has{endif} been successfully uploaded to <a href="{cfg:site_url}">{cfg:site_name}</a>.
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
                {html_file_list}
            </td>
        </tr>
        <tr>
            <td>Size</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Your transfers list</td>
            <td><a href="{cfg:site_url}?s=transfers">{cfg:site_url}?s=transfers</a></td>
        </tr>
    </tbody>
</table>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
