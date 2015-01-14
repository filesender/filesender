subject: Reminder, file{if:transfer.files>1}s{endif} available for download

{alternative:plain}

Dear Sir or Madam,

This is a reminder, the following {if:transfer.files>1}files have{else}file has{endif} been uploaded to {cfg:site_name} by {transfer.user_email} and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{transfer.files.first().name} ({size:transfer.files.first().size})
{endif}

Download link: {cfg:site_url}?s=download&token={recipient.token}

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
    This is a reminder, the following {if:transfer.files>1}files have{else}file has{endif} been uploaded to <a href="{cfg:site_url}">{cfg:site_name}</a> by <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents.
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
                        <li>{file.name} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().name} ({size:transfer.files.first().size})
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
            <td><a href="{cfg:site_url}?s=download&token={recipient.token}">{cfg:site_url}?s=download&amp;token={recipient.token}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message || transfer.subject}
<p>
    Personal message from {transfer.user_email}: {transfer.subject}
    <br /><br />
    {transfer.message}
</p>
{endif}

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
