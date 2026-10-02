<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 게스트 바우쳐를 수신
subject: {guest.subject}

{alternative:plain}

안녕하세요?

{cfg:site_name}를 이용할 수 있는 바우쳐를 수신했습니다. 이 바우쳐를 이용하면 1회에 한해 파일을 업로드하고 수신자들에게 다운로드를 허용할 수 있습니다.

발행자: {guest.user_email}
바우쳐 링크: {guest.upload_link}

{if:guest.does_not_expire}
이 바우쳐는 만료기한이 없습니다.
{else}
이 바우쳐는 {date:guest.expires}까지 이용할 수 있으며 이후에는 자동으로 삭제됩니다.
{endif}

{if:guest.message}{guest.user_email}의 메시지: {guest.message}{endif}

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
    안녕하세요?
</p>

<p>
    <a href="{cfg:site_url}">{cfg:site_name}</a>를 이용할 수 있는 바우쳐를 수신했습니다.  이 바우쳐를 이용하면 1회에 한해 파일을 업로드하고 수신자들에게 다운로드를 허용할 수 있습니다.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>발행자</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>바우쳐 링크</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">이 바우쳐는 만료기한이 없습니다</td>
{else}
            <td>유효기간</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    {guest.user_email}의 메시지:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>