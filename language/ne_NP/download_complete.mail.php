<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: डाउनलोड पूरा भयो

{alternative:plain}

प्रिय महोदय वा महोदया,

तपाईँको तलको {if:files>1}files{else}file{endif} को डाउनलोड समाप्त भएको छ:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    तपाईँको तलको {if:files>1}files{else}file{endif} को डाउनलोड समाप्त भएको छ:
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>