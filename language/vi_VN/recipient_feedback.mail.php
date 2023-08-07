<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Phản hồi từ {if:target_type=="recipient"}người nhận{endif}{if:target_type=="guest"}khách{endif} {target.email} của bạn

{thay thế:đồng bằng}

Thưa ông hoặc bà,

Chúng tôi đã nhận được phản hồi qua email từ {if:target_type=="recipient"}người nhận{endif}{if:target_type=="guest"}khách{endif} {target.email} của bạn, vui lòng xem phản hồi này kèm theo.

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     Chúng tôi đã nhận được phản hồi qua email từ {if:target_type=="recipient"}người nhận{endif}{if:target_type=="guest"}khách{endif} {target.email} của bạn, vui lòng xem phản hồi này kèm theo.
</p>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>