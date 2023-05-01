<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (リマインダー)ファイルがダウンロードできます
subject: (リマインダー){transfer.subject}

{alternative:plain}

利用者様、

これはリマインダーです。{transfer.user_email}が、次のファイルを{cfg:site_name}にアップロードしました。あなたにはそのコンテンツをダウンロードする権限が与えられています:

{if:transfer.files>1}{each:transfer.files as file}
  -{file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

ダウンロードリンク:{recipient.download_link}

トランザクションは{date:transfer.expires}に無効になり、この日以降に自動的に削除されます。

{if:transfer.message || transfer.subject}
{transfer.user_email}からの個人的なメッセージ:{transfer.subject}

{transfer.message}
{endif}

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
   これはリマインダーです。<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>が、次のファイルを<a href="{cfg:site_url}">{cfg:site_name}</a>にアップロードしました。あなたにはそのコンテンツをダウンロードする権限が与えられています:
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
       {if:transfer.files>1}
        <tr>
            <td>転送サイズ</td>
            <td>{size:transfer.size}</td>
        </tr>
       {endif}
        <tr>
            <td>この日以降無効</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>ダウンロードリンク</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
   {transfer.user_email}からの個人的なメッセージ:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
   {transfer.message}
</p>
{endif}

<p>
    以上、よろしくお願いします。<br />
   {cfg:site_name}
</p>