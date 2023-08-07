<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
பொருள்: (நினைவூட்டல்) கோப்பு{if:transfer.files>1}s{endif} பதிவிறக்கம் செய்யக் கிடைக்கிறது
பொருள்: (நினைவூட்டல்) {transfer.subject}

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

நினைவூட்டல் :transfer.files>1}அவற்றின்{else}அதன்{endif} உள்ளடக்கங்கள்:

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

வாழ்த்துகள்,
{cfg:site_name}
{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    இது ஒரு நினைவூட்டல், பின்வரும் {if:transfer.files>1}கோப்புகள் உள்ளன{else}கோப்பு{endif} <a href="{cfg:site_url}">{cfg:site_name}</a> இல் பதிவேற்றப்பட்டது <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> மூலம் {if:transfer.files>1}அவர்களின்{else}அதன்{endif} உள்ளடக்கங்களைப் பதிவிறக்க உங்களுக்கு அனுமதி வழங்கப்பட்டுள்ளது. .
</p>

<அட்டவணை விதிகள்="வரிசைகள்">
    <thead>
        <tr>
            <th colspan="2">பரிவர்த்தனை விவரங்கள்</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>கோப்பு{if:transfer.files>1}s{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                {வேறு}
                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        {if:transfer.files>1}
        <tr>
            <td>பரிமாற்ற அளவு</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>காலாவதி தேதி</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>இணைப்பைப் பதிவிறக்கு</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    {transfer.user_email} இலிருந்து தனிப்பட்ட செய்தி:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>