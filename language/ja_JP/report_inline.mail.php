<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {target.type}#{target.id}に関するレポート

{alternative:plain}

利用者様、

{target.type}に関するレポートは次のとおりです:

{target.type}番号:{target.id}

{if:target.type == "Transfer"}
この転送の全体のサイズは{size:transfer.size}で、{transfer.files}ファイルが含まれています。

この転送は、{date:transfer.expires}に無効になります／ました。

この転送は{transfer.recipients}人の受信者に送信されました。
{endif}
{if:target.type == "File"}
このファイルの名前は{file.path}、サイズは{size:file.size}で、{date:file.transfer.expires}に無効になります／ました。
{endif}
{if:target.type == "Recipient"}
この受信者のメールアドレスは{recipient.email}で、{date:recipient.expires}に無効になります／ました。
{endif}

転送の完全なログは次のとおりです:

{raw:content.plain}

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    {target.type}に関するレポートは次のとおりです: <br/><br/>

    {target.type}番号:{target.id}<br/><br/>

    {if:target.type == "Transfer"}
    この転送の全体のサイズは{size:transfer.size}で、{transfer.files}ファイルが含まれています。<br/><br/>

    この転送は、{date:transfer.expires}に無効になります／ました。<br/><br/>

    この転送は{transfer.recipients}人の受信者に送信されました。
    {endif}
    {if:target.type == "File"}
    このファイルの名前は{file.path}、サイズは{size:file.size}で、{date:file.transfer.expires}に無効になります／ました。
    {endif}
    {if:target.type == "Recipient"}
    この受信者のメールアドレスは{recipient.email}で、{date:recipient.expires}に無効になります／ました。
    {endif}
</p>

<p>
    転送の完全なログは次のとおりです:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Date</th>
            <th>Event</th>
            <th>IP address</th>
        </thead>
        <tbody>
           {raw:content.html}
        </tbody>
    </table>
</p>

<p>以上、よろしくお願いいたします。<br/>
{cfg:site_name}</p>
