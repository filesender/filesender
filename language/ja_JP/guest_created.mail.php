<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ゲストバウチャーの受信
subject: {guest.subject}

{alternative:plain}

利用者様、

{cfg:site_name}へのアクセスには、以下のバウチャーをご利用ください。このバウチャーを使用して、1セットのファイルをアップロードし、1グループの人がダウンロードできるようにすることができます。

バウチャー発行者:{guest.user_email}
バウチャーリンク:{guest.upload_link}

バウチャーは{date:guest.expires}に無効になり、この日以降に自動的に削除されます。

{if:guest.message}{guest.user_email}からの個人的なメッセージ:{guest.message}{endif}

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    <a href="{cfg:site_url}">{cfg:site_name}</a>へのアクセスには、以下のバウチャーをご利用ください。このバウチャーを使用して、1セットのファイルをアップロードし、1グループの人がダウンロードできるようにすることができます。
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">バウチャーの詳細</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>バウチャー発行者</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>バウチャーリンク</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>無効化日</td>
{if:guest.does_not_expire}
            <td>なし</td>
{else}
            <td>{date:guest.expires}</td>
{endif}
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
   {guest.user_email}からの個人的なメッセージ:
</p>
<p class="message">
   {guest.message}
</p>
{endif}

<p>
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>
