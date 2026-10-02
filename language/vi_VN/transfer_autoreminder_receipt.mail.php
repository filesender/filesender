<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Đã gửi lời nhắc tự động cho lô hàng tệp n°{transfer.id}

{thay thế:đồng bằng}

Thưa ông hoặc bà,

Một lời nhắc tự động đã được gửi tới những người nhận không tải tệp xuống từ quá trình chuyển của bạn n°{transfer.id} trên {cfg:site_name} ({transfer.link}):

{mỗi:người nhận là người nhận}
   - {người nhận E-mail}
{endeach}

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     Một lời nhắc tự động đã được gửi tới những người nhận không tải xuống các tệp từ <a href="{transfer.link}">chuyển n°{transfer.id}</a> của bạn trên <a href="{cfg:site_url}" >{cfg:site_name}</a> :
</p>

<p>
     <ul>
     {mỗi:người nhận là người nhận}
       <li>{recipient.email}</li>
     {endeach}
     </ul>
</p>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>