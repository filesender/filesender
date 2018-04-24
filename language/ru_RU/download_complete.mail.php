subject: Скачивание завершено

{alternative:plain}

Товарищ!

Скачивание {if:files>1}Файлов{else}файла{endif} завершено:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().name} ({size:files.first().size})
{endif}

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
    Товарищ!
</p>

<p>
    Скачивание {if:files>1}Файлов{else}файла{endif} завершено:
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().name} ({size:files.first().size})
    {endif}
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
