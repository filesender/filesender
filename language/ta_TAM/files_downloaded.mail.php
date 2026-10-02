<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
பொருள்: ரசீது பதிவிறக்கம்

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

நீங்கள் பதிவேற்றிய {if:files>1}பல கோப்புகள்{else}ஒரு கோப்பு{endif} {if:files>1}உள்ளது{else}{endif} {recipient.email} மூலம் {cfg:site_name} இலிருந்து பதிவிறக்கப்பட்டது :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

{files.first().transfer.link} இல் உள்ள இடமாற்றங்கள் பக்கத்தில் உங்கள் கோப்புகளை அணுகலாம் மற்றும் விரிவான பதிவிறக்க புள்ளிவிவரங்களைப் பார்க்கலாம்.

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    நீங்கள் பதிவேற்றிய {if:files>1}பல கோப்புகள்{else}ஒரு கோப்பு{endif} {if:files>1}உள்ளது{else}{endif} {recipient.email} மூலம் {cfg:site_name} இலிருந்து பதிவிறக்கம் செய்யப்பட்டுள்ளது.
</p>

<p>
    {if:files>1}
    <ul>
        {ஒவ்வொன்றும்:கோப்பாக கோப்புகள்}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {வேறு}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    <a href="{files.first().transfer.link}">{files.first().transfer.link}</a> இல் உங்கள் கோப்புகளை அணுகலாம் மற்றும் பரிமாற்றங்கள் பக்கத்தில் விரிவான பதிவிறக்க புள்ளிவிவரங்களைப் பார்க்கலாம்.
</p>

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>