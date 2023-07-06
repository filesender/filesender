<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Chuyển tổng kết hàng ngày

{thay thế:đồng bằng}

Thưa ông hoặc bà,

Vui lòng xem bên dưới bản tóm tắt các bản tải xuống cho quá trình chuyển {transfer.id} của bạn (đã tải lên {date:transfer.created}):

{nếu:sự kiện}
{mỗi:sự kiện là sự kiện}
   - Người nhận {event.who} đã tải xuống {if:event.what == "archive"}archive{else}tệp {event.what_name}{endif} vào {datetime:event.when}
{endeach}
{khác}
Không có lượt tải xuống nào
{endif}

Bạn có thể tìm thêm chi tiết tại {transfer.link}

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     Vui lòng xem bên dưới bản tóm tắt các bản tải xuống cho quá trình chuyển {transfer.id} của bạn (đã tải lên {date:transfer.created}):
</p>

{nếu:sự kiện}
<ul>
{mỗi:sự kiện là sự kiện}
     <li>Người nhận {event.who} đã tải xuống {if:event.what == "archive"}bản lưu trữ{else}tệp {event.what_name}{endif} vào {datetime:event.when}</li>
{endeach}
</ul>
{khác}
<p>
     Không có lượt tải xuống nào
</p>
{endif}

<p>
     Bạn có thể tìm thêm thông tin chi tiết tại <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>