<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: สรุปยอดโอนประจำวัน

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

โปรดดูข้อมูลสรุปการดาวน์โหลดสำหรับการโอนเงินของคุณด้านล่าง {transfer.id} (อัปโหลดเมื่อ {วันที่:transfer.created}) :

{หาก:เหตุการณ์}
{แต่ละเหตุการณ์เป็นเหตุการณ์}
   - ผู้รับ {event.who} ดาวน์โหลด {if:event.what == "archive"}archive{else}file {event.what_name}{endif} เมื่อ {datetime:event.when}
{จบ}
{อื่น}
ไม่มีการดาวน์โหลด
{endif}

คุณสามารถดูรายละเอียดเพิ่มเติมได้ที่ {transfer.link}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     โปรดดูข้อมูลสรุปการดาวน์โหลดสำหรับการโอนเงินของคุณด้านล่าง {transfer.id} (อัปโหลดเมื่อ {วันที่:transfer.created}) :
</p>

{หาก:เหตุการณ์}
<ul>
{แต่ละเหตุการณ์เป็นเหตุการณ์}
     <li>ผู้รับ {event.who} ดาวน์โหลด {if:event.what == "archive"}archive{else}file {event.what_name}{endif} เมื่อ {datetime:event.when}</li>
{จบ}
</ul>
{อื่น}
<p>
     ไม่มีการดาวน์โหลด
</p>
{endif}

<p>
     คุณสามารถดูรายละเอียดเพิ่มเติมได้ที่ <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>