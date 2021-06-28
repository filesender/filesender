<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ファイルが正常にアップロードされました

{alternative:plain}

利用者様、

次のファイルは{cfg:site_name}に正常にアップロードされました。

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

詳細情報:{transfer.link}

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
   次のファイルは<a href="{cfg:site_url}">{cfg:site_name}</a>に正常にアップロードされました。
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">トランザクションの詳細</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>ファイル</td>
            <td>
               {if:transfer.files>1}
                <ul>
                   {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                   {endeach}
                </ul>
               {else}
               {transfer.files.first().path} ({size:transfer.files.first().size})
               {endif}
            </td>
        </tr>
        <tr>
            <td>サイズ</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>詳細情報</ td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    以上、よろしくお願いいたします。<br/>
   {cfg:site_name}
</p>