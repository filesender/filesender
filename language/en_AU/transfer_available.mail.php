subject: File{if:transfer.files>1}s{endif} available for download
subject: {transfer.subject}

{alternative:plain}

Dear Sir or Madam,

The following {if:transfer.files>1}files have{else}file has{endif} been uploaded to {cfg:site_name} by {transfer.user_email} and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Download link: {recipient.download_link}

The transaction is available until {date:transfer.expires} after which time it will be automatically deleted.

{if:transfer.message || transfer.subject}
Personal message from {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

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
            <td>Transfer size</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Expiry date</td>
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
    Personal message from {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
