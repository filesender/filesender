<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
தலைப்பு: பதிவிறக்கம் முடிந்தது

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

கீழே உள்ள {if:files>1}கோப்புகளின்{else}கோப்பின்{endif} உங்கள் பதிவிறக்கம் முடிந்தது:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    கீழே உள்ள {if:files>1}கோப்புகளின்{else}கோப்பின்{endif} உங்கள் பதிவிறக்கம் முடிந்தது:
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
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>