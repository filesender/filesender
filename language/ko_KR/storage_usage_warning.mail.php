<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: 스토리지 사용 경고

{alternative:plain}

안녕하세요?

{cfg:site_name}의 스토리지 사용을 경고합니다:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space})의 스토리지 공간이 {size:warning.free_space} ({warning.free_space_pct}%)만 남았습니다
{endeach}

더 자세한 사항은 {cfg:site_url}에서 확인할 수 있습니다

감사합니다,
{cfg:site_name}

{alternative:html}

<p>
안녕하세요?
</p>

<p>
    {cfg:site_name}의 스토리지 사용을 경고합니다:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space})의 스토리지 공간이 {size:warning.free_space} ({warning.free_space_pct}%)만 남았습니다</li>
{endeach}
</ul>

<p>
    더 자세한 사항은 <a href="{cfg:site_url}">{cfg:site_url}</a>에서 확인하실 수 있습니다
</p>

<p>
    감사합니다,<br />
    {cfg:site_name}
</p>

