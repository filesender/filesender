<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: ความคิดเห็นจาก {if:target_type=="recipient"}ผู้รับ{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

เราได้รับอีเมลตอบกลับจาก {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email} โปรดดูที่แนบมา

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     เราได้รับอีเมลตอบกลับจาก {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email} โปรดดูที่แนบมา
</p>

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>