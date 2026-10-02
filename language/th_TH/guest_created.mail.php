<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: ได้รับบัตรกำนัลผู้เข้าพัก
เรื่อง: {guest.subject}

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

โปรดดูบัตรกำนัลที่ให้สิทธิ์เข้าถึง {cfg:site_name} ด้านล่าง คุณสามารถใช้บัตรกำนัลนี้เพื่ออัปโหลดไฟล์หนึ่งชุดและทำให้พร้อมสำหรับการดาวน์โหลดสำหรับกลุ่มคน

ผู้ออก: {guest.user_email}
ลิงก์บัตรกำนัล: {guest.upload_link}

{หาก:guest.does_not_expire}
บัตรกำนัลนี้ไม่มีวันหมดอายุ
{อื่น}
เวาเชอร์สามารถใช้ได้ถึงวันที่ {date:guest.expires} หลังจากนั้นจะถูกลบโดยอัตโนมัติ
{endif}

{if:guest.message}ข้อความส่วนตัวจาก {guest.user_email}: {guest.message}{endif}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     โปรดดูบัตรกำนัลด้านล่างที่ให้สิทธิ์เข้าถึง <a href="{cfg:site_url}">{cfg:site_name}</a> คุณสามารถใช้บัตรกำนัลนี้เพื่ออัปโหลดไฟล์หนึ่งชุดและทำให้พร้อมสำหรับการดาวน์โหลดสำหรับกลุ่มคน
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
{หาก:guest.does_not_expire}
             <td colspan="2">คำเชิญนี้ไม่มีวันหมดอายุ</td>
{อื่น}
             <td>ใช้ได้จนถึง</td>
             <td>{วันที่:guest.expires}</td>
{endif}

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