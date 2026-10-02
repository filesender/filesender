<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: ส่งการแจ้งเตือนอัตโนมัติสำหรับการจัดส่งไฟล์ n°{transfer.id}

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

การแจ้งเตือนอัตโนมัติถูกส่งไปยังผู้รับที่ไม่ได้ดาวน์โหลดไฟล์จากการโอนของคุณ n°{transfer.id} บน {cfg:site_name} ({transfer.link}) :

{แต่ละ:ผู้รับเป็นผู้รับ}
   - {recipient.email}
{จบ}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     การแจ้งเตือนอัตโนมัติถูกส่งไปยังผู้รับที่ไม่ได้ดาวน์โหลดไฟล์จาก <a href="{transfer.link}">transfer n°{transfer.id}</a> ของคุณบน <a href="{cfg:site_url}" >{cfg:site_name}</a> :
</p>

<p>
     <ul>
     {แต่ละ:ผู้รับเป็นผู้รับ}
       <li>{recipient.email}</li>
     {จบ}
     </ul>
</p>

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>