<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Download receipt

{alternative:plain}

Hello,

{if:files>1}Several files{else}A file{endif} you uploaded {if:files>1}have{else}has{endif} been downloaded from {cfg:site_name} by {if:files.first().transfer.get_a_link}a transfer link:{else}{recipient.email} :{endif}

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

You can access your files and view detailed download statistics on the transfers page at {files.first().transfer.link}.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Hello,
</p>

<p>
    {if:files>1}Several files{else}A file{endif} you uploaded {if:files>1}have{else}has{endif} been downloaded from {cfg:site_name} by {if:files.first().transfer.get_a_link}a transfer link.{else}{recipient.email}{endif}
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
    You can access your files and view detailed download statistics on the transfers page at <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
