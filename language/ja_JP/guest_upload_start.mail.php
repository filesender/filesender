<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ゲストがファイルのアップロードを開始

{alternative:plain}

利用者様、

次のゲストがバウチャーからファイルのアップロードを開始しました:

ゲスト:{guest.email}
バウチャーリンク:{cfg:site_url}?s=upload&vid={guest.token}

バウチャーは{date:guest.expires}に無効になり、この日以降に自動的に削除されます。

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    次のゲストがバウチャーからファイルのアップロードを開始しました:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">バウチャーの詳細</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>ゲスト</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>バウチャーリンク</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>無効化日</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>
