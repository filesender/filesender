<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
தலைப்பு: கோப்பு{if:transfer.files>1}s{endif} வெற்றிகரமாக பதிவேற்றப்பட்டது

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

பின்வரும் {if:transfer.files>1}கோப்புகள் உள்ளன{else}கோப்பு{endif} வெற்றிகரமாக {cfg:site_name} இல் பதிவேற்றப்பட்டது.

பின்வரும் இணைப்பைப் பயன்படுத்தி இந்தக் கோப்புகளைப் பதிவிறக்கலாம்: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files கோப்பாக}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

மேலும் தகவல்: {transfer.link}

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    பின்வரும் {if:transfer.files>1}கோப்புகள் உள்ளன{else}கோப்பு{endif} <a href="{cfg:site_url}">{cfg:site_name}</a> க்கு வெற்றிகரமாக பதிவேற்றப்பட்டது.
</p>

<p>
இந்தக் கோப்புகளை பின்வரும் இணைப்பைப் பயன்படுத்தி பதிவிறக்கம் செய்யலாம் <a href="{transfer.download_link}">{transfer.download_link}</a>
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
        <tr>
            <td>அளவு</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>மேலும் தகவல்</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>