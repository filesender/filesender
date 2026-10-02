<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Tải về hóa đơn

{thay thế:đồng bằng}

Thưa ông hoặc bà,

{if:files>1}Một số tệp{else}Một tệp{endif} bạn đã tải lên {if:files>1}have{else}has{endif} đã được {recipient.email} tải xuống từ {cfg:site_name} :

{if:files>1}{each:files as file}
   - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Bạn có thể truy cập các tệp của mình và xem số liệu thống kê tải xuống chi tiết trên trang chuyển tại {files.first().transfer.link}.

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     {if:files>1}Một vài tệp{else}Một tệp{endif} bạn đã tải lên {if:files>1}have{else}has{endif} đã được {recipient.email} tải xuống từ {cfg:site_name}.
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
     Bạn có thể truy cập tệp của mình và xem số liệu thống kê tải xuống chi tiết trên trang chuyển tại <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>