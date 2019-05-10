<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (reminder) File{if:transfer.files>1}s{endif} available for download
subject: (reminder) {transfer.subject}

{alternative:plain}

Dear Sir or Madam,

This is a reminder, the following {if:transfer.files>1}files have{else}file has{endif} been uploaded to {cfg:site_name} by {transfer.user_email} and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents :

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

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
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
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

</td></tr>

 <tr style="border-style:none">
    <td align="center">
       <p style="font-size:12px; text-decoration:none">
       More information about the SURFfilesender service can be found at
       <a rel="nofollow" href="https://www.surffilesender.nl/en/" target="_blank">www.surffilesender.nl/en</a>
       </p>
       <p style="font-size:10px; text-decoration:none"> SURFfilesender is powered by <a rel="nofollow" href="https://www.surf.nl/en/" target="_blank">SURF</a>.
       </p>
    </td>
</tr>
</table>
