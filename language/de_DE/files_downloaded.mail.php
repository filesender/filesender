Betreff: Download Empfänger

{alternative:plain}

Sehr geehrte Damen und Herren,

{if:files>1}mehrere Dateien{else}eine Datei{endif} die Sie hochgeladen haben {if:files>1}wurden{else}wurde{endif} von {cfg:site_name} über {recipient.email} heruntergeladen:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Sie können auf Ihre Dateien zugreifen und sich eine detaillierte Download-Statistik auf der Übertragungsseite anzeigen lassen {files.first().transfer.link}.

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    {if:files>1}mehrere Dateien{else}eine Datei{endif} die Sie hochgeladen haben {if:files>1}wurden{else}wurde{endif} von {cfg:site_name} über {recipient.email} heruntergeladen :
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
    Sie können auf Ihre Dateien zugreifen und sich eine detaillierte Download-Statistik auf der Übertragungsseite anzeigen lassen <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Mit freundlichen Grüßen,<br />
    {cfg:site_name}
</p>
