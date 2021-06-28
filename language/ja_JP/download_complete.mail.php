<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ダウンロードの完了

{alternative:plain}

利用者様、

以下のファイルのダウンロードが終了しました:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

以上、よろしくお願いします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    以下のファイルのダウンロードが終了しました:
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
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>