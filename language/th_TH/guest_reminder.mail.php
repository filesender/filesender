<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: (เตือนความจำ) ได้รับบัตรกำนัลแขกแล้ว
เรื่อง: (เตือนความจำ) {guest.subject}

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

นี่เป็นการช่วยเตือน โปรดค้นหาบัตรกำนัลที่ให้สิทธิ์เข้าถึง {cfg:site_name} ด้านล่าง คุณสามารถใช้บัตรกำนัลนี้เพื่ออัปโหลดไฟล์หนึ่งชุดและทำให้พร้อมสำหรับการดาวน์โหลดสำหรับกลุ่มคน

ผู้ออก: {guest.user_email}
ลิงก์บัตรกำนัล: {guest.upload_link}

เวาเชอร์สามารถใช้ได้ถึงวันที่ {date:guest.expires} หลังจากนั้นจะถูกลบโดยอัตโนมัติ

{if:guest.message}ข้อความส่วนตัวจาก {guest.user_email}: {guest.message}{endif}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     นี่เป็นการช่วยเตือน โปรดค้นหาบัตรกำนัลที่ให้สิทธิ์เข้าถึง <a href="{cfg:site_url}">{cfg:site_name}</a> ด้านล่าง คุณสามารถใช้บัตรกำนัลนี้เพื่ออัปโหลดไฟล์หนึ่งชุดและทำให้พร้อมสำหรับการดาวน์โหลดสำหรับกลุ่มคน
</p>

<กฎตาราง = "แถว">
     <ส่วนหัว>
         <tr>
             <th colspan="2">รายละเอียดบัตรกำนัล</th>
         </tr>
     </thead>
     <tbody>
         <tr>
             <td>ผู้ออก</td>
             <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
         </tr>
         <tr>
             <td>ลิงก์บัตรกำนัล</td>
             <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
         </tr>
         <tr>
             <td>ใช้ได้จนถึง</td>
             <td>{วันที่:guest.expires}</td>
         </tr>
     </tbody>
</ตาราง>

{หาก:guest.message}
<p>
     ข้อความส่วนตัวจาก {guest.user_email}:
</p>
<p class="ข้อความ">
     {guest.message}
</p>
{endif}

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>