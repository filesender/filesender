<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: รายงานเกี่ยวกับ {target.type} #{target.id}

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

นี่คือรายงานเกี่ยวกับ {target.type} ของคุณ:

{target.type} หมายเลข : {target.id}

{if:target.type == "การโอน"}
การถ่ายโอนนี้มีไฟล์ {transfer.files} ที่มีขนาดโดยรวมเท่ากับ {size:transfer.size}

การโอนนี้ใช้ได้จนถึง {date:transfer.expires}

การโอนนี้ถูกส่งไปยังผู้รับ {transfer.recipients} คน
{endif}
{if:target.type == "ไฟล์"}
ไฟล์นี้ชื่อ {file.path} มีขนาด {size:file.size} และใช้ได้จนถึง {date:file.transfer.expires}
{endif}
{if:target.type == "ผู้รับ"}
ผู้รับนี้มีที่อยู่อีเมล {recipient.email} และใช้ได้ถึงวันที่ {date:recipient.expires}
{endif}

นี่คือบันทึกทั้งหมดของสิ่งที่เกิดขึ้นกับการโอน:

{ดิบ:content.plain}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     นี่คือรายงานเกี่ยวกับ {target.type} ของคุณ:<br /><br />
    
     {target.type} หมายเลข : {target.id}<br /><br />
    
     {if:target.type == "การโอน"}
     การถ่ายโอนนี้มีไฟล์ {transfer.files} ที่มีขนาดโดยรวมเท่ากับ {size:transfer.size}<br /><br />
    
     การโอนนี้ใช้ได้จนถึง {date:transfer.expires}.<br /><br />
    
     การโอนนี้ถูกส่งไปยังผู้รับ {transfer.recipients} คน
     {endif}
     {if:target.type == "ไฟล์"}
     ไฟล์นี้ชื่อ {file.path} มีขนาด {size:file.size} และใช้ได้จนถึง {date:file.transfer.expires}
     {endif}
     {if:target.type == "ผู้รับ"}
     ผู้รับนี้มีที่อยู่อีเมล {recipient.email} และใช้ได้ถึงวันที่ {date:recipient.expires}
     {endif}
</p>

<p>
     นี่คือบันทึกทั้งหมดของสิ่งที่เกิดขึ้นกับการโอน:
     <ตาราง class="auditlog" กฎ="แถว">
         <ส่วนหัว>
             <th>วันที่</th>
             <th>เหตุการณ์</th>
             <th>ที่อยู่ IP</th>
         </thead>
         <tbody>
             {ข้อมูลดิบ:content.html}
         </tbody>
     </ตาราง>
</p>

<p>ขอแสดงความนับถือ<br/>
{cfg:site_name}</p>