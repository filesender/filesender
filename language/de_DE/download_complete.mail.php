Betreff: Download abgeschlossen

{alternative:plain}

Sehr geehrte Damen und Herren,

ihr Download von {if:files>1}files{else}file{endif} ist beendet:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    ihr Download von {if:files>1}files{else}file{endif} ist beendet :
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
    Mit freundliche Grüßen,<br />
    {cfg:site_name}
</p>
