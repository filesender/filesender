<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: แขกสิ้นสุดการอัปโหลดไฟล์

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

แขกต่อไปนี้ลงเอยด้วยการอัปโหลดไฟล์จากเวาเชอร์ของคุณ:

แขก: {guest.email}
ลิงก์บัตรกำนัล: {cfg:site_url}?s=upload&vid={guest.token}

เวาเชอร์สามารถใช้ได้ถึงวันที่ {date:guest.expires} หลังจากนั้นจะถูกลบโดยอัตโนมัติ

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     แขกต่อไปนี้ลงเอยด้วยการอัปโหลดไฟล์จากเวาเชอร์ของคุณ:
</p>

<กฎตาราง = "แถว">
     <ส่วนหัว>
         <tr>
             <th colspan="2">รายละเอียดบัตรกำนัล</th>
         </tr>
     </thead>
     <tbody>
         <tr>
             <td>แขกรับเชิญ</td>
             <td><a href="mailto:{guest.email}">{guest.email}</a></td>
         </tr>
         <tr>
             <td>ลิงก์บัตรกำนัล</td>
             <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
         </tr>
         <tr>
             <td>ใช้ได้จนถึง</td>
             <td>{วันที่:guest.expires}</td>
         </tr>
     </tbody>
</ตาราง>

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>