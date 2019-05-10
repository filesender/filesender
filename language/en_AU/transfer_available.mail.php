<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: File{if:transfer.files>1}s{endif} available for download
subject: {transfer.subject}

{alternative:plain}

Dear Sir or Madam,

The following {if:transfer.files>1}files have{else}file has{endif} been uploaded to {cfg:site_name} by {transfer.user_email} and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents:

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
    The following {if:transfer.files>1}files have{else}file has{endif} been uploaded to <a href="{cfg:site_url}">{cfg:site_name}</a> by <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents.
</p>

<table style="width:800" align="center" rules="rows">
        <tr>

            <th colspan="2">Transaction details</th>
        </tr>
        <tr>
            <td style="width:100">File{if:transfer.files>1}s{endif}:</td>
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
            <td style="width:125">Transfer size:</td>
            <td>{size:transfer.size}</td>
        </tr>
        {if:transfer.message}
        <tr>
            <td style="width:125">Personal message:</td>
            <td>{transfer.message}</td>
        </tr>
        {endif}
</table>

<br>&nbsp;<br />
<table style="width:800; border-radius:5px; border:0px; padding:0px" align="center">
  <tr>
<td style="width:300">&nbsp;</td>
<td align="center"; style="width:200; color: #ffffff; background-color: #ed6b06; display: block; font-family: Arial, sans-serif; font-size: 20px; font-style: normal; color:#ffffff; text-align: center;
text-decoration: none; word-spacing: 0px; border-radius: 10px; padding: 15px 20px">
<a href="{recipient.download_link}" target="_blank" style="text-decoration:none">
Download File{if:transfer.files>1}s{endif}<br>
  </td>
<td style="width:300">&nbsp;</td>
  </tr>
</table>

<p style="font-size:14px; text-decoration:none; color:Tomato" align="center">Download the file{if:transfer.files>1}s{endif} before:<br>{date:transfer.expires}</p>
<p>&nbsp;</p>

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
