<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: ストレージ使用量の警告

{alternative:plain}

利用者様、

{cfg:site_name}のストレージ使用量についての警告です:

{each:warnings as warning}
   -{warning.filesystem}({size:warning.total_space})は、残り{size:warning.free_space}のみです({warning.free_space_pct}%)
{endeach}

次のURLから追加の詳細をご覧になれます：{cfg:site_url}

以上、よろしくお願いいたします。
{cfg:site_name}

{alternative:html}

<p>
    利用者様、
</p>

<p>
    {cfg:site_name}のストレージ使用量についての警告です:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem}({size:warning.total_space})は、残り{size:warning.free_space}のみです({warning.free_space_pct}％)</li>
{endeach}
</ul>

<p>
    次のURLから追加の詳細をご覧になれます：<a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    以上、よろしくお願いいたします。<br/>
    {cfg:site_name}
</p>