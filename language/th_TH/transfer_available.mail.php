<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: ไฟล์{if:transfer.files>1}s{endif} พร้อมสำหรับการดาวน์โหลด
หัวเรื่อง: {transfer.subject}

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

{if:transfer.files>1}ไฟล์มี{else}file has{endif} ต่อไปนี้ได้รับการอัปโหลดไปยัง {cfg:site_name} โดย {transfer.user_email} และคุณได้รับอนุญาตให้ดาวน์โหลด {if:transfer.files> 1}เนื้อหา{else}its{endif}ของพวกเขา :

{if:transfer.files>1}{each:transfer.files เป็นไฟล์}
   - {file.path} ({ขนาด:ไฟล์.ขนาด})
{endeach}{else}
{transfer.files.first().path} ({ขนาด:transfer.files.first().size})
{endif}

ลิงก์ดาวน์โหลด: {recipient.download_link}

การทำธุรกรรมจะดำเนินไปจนถึงวันที่ {date:transfer.expires} หลังจากนั้นจะถูกลบโดยอัตโนมัติ

{หาก:transfer.message || โอนเรื่อง}
ข้อความส่วนตัวจาก {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     {if:transfer.files>1}ไฟล์มี{else}ไฟล์แล้ว{endif} ต่อไปนี้ถูกอัปโหลดไปยัง <a href="{cfg:site_url}">{cfg:site_name}</a> โดย <a href= "mailto:{transfer.user_email}">{transfer.user_email}</a> และคุณได้รับอนุญาตให้ดาวน์โหลดเนื้อหา {if:transfer.files>1}ของพวกเขา{else}its{endif}
</p>

<กฎตาราง = "แถว">
     <ส่วนหัว>
         <tr>
             <th colspan="2">รายละเอียดธุรกรรม</th>
         </tr>
     </thead>
     <tbody>
         <tr>
             <td>ไฟล์{if:transfer.files>1}s{endif}</td>
             <td>
                 {หาก:transfer.files>1}
                 <ul>
                     {แต่ละ:transfer.files เป็นไฟล์}
                         <li>{file.path} ({ขนาด:file.size})</li>
                     {จบ}
                 </ul>
                 {อื่น}
                 {transfer.files.first().path} ({ขนาด:transfer.files.first().size})
                 {endif}
             </td>
         </tr>
         {หาก:transfer.files>1}
         <tr>
             <td>ขนาดการถ่ายโอน</td>
             <td>{ขนาด:transfer.size}</td>
         </tr>
         {endif}
         <tr>
             <td>วันหมดอายุ</td>
             <td>{วันที่:transfer.expires}</td>
         </tr>
         <tr>
             <td>ลิงค์ดาวน์โหลด</td>
             <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
         </tr>
     </tbody>
</ตาราง>

{หาก:transfer.message}
<p>
     ข้อความส่วนตัวจาก {transfer.user_email}:
</p>
<p class="ข้อความ">
     {transfer.message}
</p>
{endif}

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>