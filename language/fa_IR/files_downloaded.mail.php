<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
گیرنده‌ی دانلود

{alternative:plain}

آقا / خانم
{if:files>1}فایلهایی که{else}فایلی که {endif} که آپلود کردید {if:files>1}دانلود شدند{else}دانلود شد{endif}  {cfg:site_name} به وسیله {recipient.email} :

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
    Dear Sir or Madam,
</p>

<p>
    {if:files>1}Several files{else}A file{endif} you uploaded {if:files>1}have{else}has{endif} been downloaded from {cfg:site_name} by {recipient.email}.
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