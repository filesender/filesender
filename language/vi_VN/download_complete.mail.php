<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Tải xuống hoàn tất

{thay thế:đồng bằng}

Thưa ông hoặc bà,

Quá trình tải xuống {if:files>1}tệp{else}tệp{endif} dưới đây của bạn đã kết thúc :

{if:files>1}{each:files as file}
   - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     Quá trình tải xuống {if:files>1}tệp{else}tệp{endif} dưới đây của bạn đã kết thúc :
</p>

<p>
     {nếu:tệp>1}
     <ul>
         {mỗi:tệp dưới dạng tệp}
             <li>{file.path} ({size:file.size})</li>
         {endeach}
     </ul>
     {khác}
     {files.first().path} ({size:files.first().size})
     {endif}
</p>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>