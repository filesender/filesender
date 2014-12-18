subject: {cfg:site_name}: Download Complete

{alternative:plain}

Dear Sir or Madam,

Your download of the {if:files>1}files{else}file{endif} bellow has ended :

{if:files>1}{each:files as file}
  - {file.name} ({size:file.size})
{endeach}{else}
{files.first().name} ({size:files.first().size})
{endif}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    Your download of the {if:files>1}files{else}file{endif} bellow has ended :
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.name} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().name} ({size:files.first().size})
    {endif}
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
