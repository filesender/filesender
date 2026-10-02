<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 게스트가 파일 업로드를 시작함

{alternative:plain}

안녕하세요?

다음 게스트가 바우쳐를 이용해 파일 업로드를 시작했습니다 :

게스트: {guest.email}
바우쳐 링크: {cfg:site_url}?s=upload&vid={guest.token}

바우쳐는 {date:guest.expires}까지 유효하고 이후에는 자동으로 삭제됩니다.

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
  안녕하세요?
</p>

<p>
다음 게스트가 바우쳐를 이용해 파일 업로드를 시작했습니다 :
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Voucher details</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>게스트</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>바우쳐 링크</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>유효기간</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>
