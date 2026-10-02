<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Cảnh báo sử dụng bộ nhớ

{thay thế:đồng bằng}

Thưa ông hoặc bà,

Việc sử dụng bộ nhớ của {cfg:site_name} là cảnh báo :

{mỗi:cảnh báo như cảnh báo}
   - {warning.filesystem} ({size:warning.total_space}) chỉ còn lại {size:warning.free_space} ({warning.free_space_pct}%)
{endeach}

Bạn có thể tìm thêm chi tiết tại {cfg:site_url}

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     Việc sử dụng bộ nhớ của {cfg:site_name} là cảnh báo :
</p>

<ul>
{mỗi:cảnh báo như cảnh báo}
     <li>{warning.filesystem} ({size:warning.total_space}) chỉ còn {size:warning.free_space} ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
     Bạn có thể tìm thêm thông tin chi tiết tại <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>