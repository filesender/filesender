<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: การส่งข้อความล้มเหลว

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

ผู้รับของคุณอย่างน้อยหนึ่งรายไม่ได้รับข้อความของคุณ :

{แต่ละ: เด้งเป็นเด้ง}
{if:bounce.target_type=="ผู้รับ"}
   - โอน #{bounce.target.transfer.id} ผู้รับ {bounce.target.email} เมื่อ {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="แขก"}
   - แขก {bounce.target.email} ในวันที่ {datetime:bounce.date}
{endif}
{จบ}

คุณสามารถดูรายละเอียดเพิ่มเติมได้ที่ {cfg:site_url}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     ผู้รับของคุณอย่างน้อยหนึ่งรายไม่ได้รับข้อความของคุณ :
</p>

<ul>
{แต่ละ: เด้งเป็นเด้ง}
     <li>
     {if:bounce.target_type=="ผู้รับ"}
         <a href="{bounce.target.transfer.link}">โอน #{bounce.target.transfer.id}</a> ผู้รับ {bounce.target.email} ในวันที่ {datetime:bounce.date}
     {endif}{if:bounce.target_type=="แขก"}
         แขก {bounce.target.email} ในวันที่ {datetime:bounce.date}
     {endif}
     </li>
{จบ}
</ul>

<p>
     คุณสามารถดูรายละเอียดเพิ่มเติมได้ที่ <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>