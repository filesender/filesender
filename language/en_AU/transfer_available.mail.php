subject: {cfg:site_name}: File{if:transfer.files>1}s{endif} available for download

{alternative:plain}

Dear Sir or Madam,

The following {if:transfer.files>1}files have{else}file has{endif} been uploaded to {cfg:site_name} by {transfer.user_email} and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents :

{text_file_list}

Download link: {cfg:site_url}?s=download&token={recipient.token}

Files:
{fileinfo}
The transaction is available until {date:transfer.expires} after which time it will be automatically deleted.

{if:transfer.message}Personal message from {transfer.user_email}: {transfer.message}{endif}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    The following {if:transfer.files>1}files have{else}file has{endif} been uploaded to <a href="{cfg:site_url}">{cfg:site_name}</a> by <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Transaction details</th>
        </tr>
    </thead>
    <tbody>
        <tr class="odd">
            <td>File{if:transfer.files>1}s{endif}</td>
            <td>
                {html_file_list}
            </td>
        </tr>
        <tr>
            <td>Size</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr class="odd">
            <td>Expiry date</td>
            <td>{date:transfer.expiry}</td>
        </tr>
        <tr>
            <td>Download link</td>
            <td><a href="{cfg:site_url}?s=download&token={recipient.token}">{cfg:site_url}?s=download&token={recipient.token}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Personal message from {transfer.user_email}: {transfer.message}
</p>
{endif}

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
