<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Lỗi gửi tin nhắn

{thay thế:đồng bằng}

Thưa ông hoặc bà,

Một hoặc nhiều người nhận của bạn không nhận được (các) tin nhắn của bạn :

{mỗi:nảy khi thoát}
{if:bounce.target_type=="Recipient"}
   - Chuyển số người nhận {bounce.target.transfer.id} {bounce.target.email} vào {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Khách"}
   - Khách {bounce.target.email} vào {datetime:bounce.date}
{endif}
{endeach}

Bạn có thể tìm thêm chi tiết tại {cfg:site_url}

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     Một hoặc nhiều người nhận của bạn không nhận được (các) tin nhắn của bạn :
</p>

<ul>
{mỗi:nảy khi thoát}
     <li>
     {if:bounce.target_type=="Recipient"}
         <a href="{bounce.target.transfer.link}">Chuyển #{bounce.target.transfer.id}</a> người nhận {bounce.target.email} vào {datetime:bounce.date}
     {endif}{if:bounce.target_type=="Khách"}
         Khách {bounce.target.email} vào {datetime:bounce.date}
     {endif}
     </li>
{endeach}
</ul>

<p>
     Bạn có thể tìm thêm thông tin chi tiết tại <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>