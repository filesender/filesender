<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
தலைப்பு: கோப்பு{if:transfer.files>1}கள்{endif} பதிவிறக்கம் செய்யக் கிடைக்கிறது
தலைப்பு: {transfer.subject}

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

பின்வரும் {if:transfer.files>1}கோப்புகள் உள்ளன{else}கோப்பு{endif} {cfg:site_name} க்கு {transfer.user_email} மூலம் பதிவேற்றப்பட்டது, மேலும் {if:transfer.files> பதிவிறக்குவதற்கான அனுமதி உங்களுக்கு வழங்கப்பட்டுள்ளது 1}அவற்றின்{else}அதன்{endif} உள்ளடக்கங்கள்:

{if:transfer.files>1}{each:transfer.files கோப்பாக}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

பதிவிறக்க இணைப்பு: {recipient.download_link}

பரிவர்த்தனை {date:transfer.expires} வரை இருக்கும், அதன் பிறகு அது தானாகவே நீக்கப்படும்.

{if:transfer.message || transfer.subject}
{transfer.user_email} இலிருந்து தனிப்பட்ட செய்தி: {transfer.subject}

{transfer.message}
{endif}

அன்புடன்,
{cfg:site_name}

{மாற்று:html}
<p>
    Dear Sir or Madam,
</p>

<p>
    The following {if:transfer.files>1}files have{else}file has{endif} been uploaded to <a href="{cfg:site_url}">{cfg:site_name}</a> by <a href= "mailto:{transfer.user_email}">{transfer.user_email}</a> and you have been granted permission to download {if:transfer.files>1}their{else}its{endif} contents.
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
                <ul><li>
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