<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: ไฟล์{if:transfer.files>1}s{endif} อัปโหลดสำเร็จแล้ว

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

{if:transfer.files>1}ไฟล์มี{else}file has{endif} ต่อไปนี้อัปโหลดไปยัง {cfg:site_name} เรียบร้อยแล้ว

ไฟล์เหล่านี้สามารถดาวน์โหลดได้โดยใช้ลิงก์ต่อไปนี้: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files เป็นไฟล์}
   - {file.path} ({ขนาด:ไฟล์.ขนาด})
{endeach}{else}
{transfer.files.first().path} ({ขนาด:transfer.files.first().size})
{endif}

ข้อมูลเพิ่มเติม: {transfer.link}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     {if:transfer.files>1}ไฟล์มี{else}file has{endif} ต่อไปนี้อัปโหลดไปยัง <a href="{cfg:site_url}">{cfg:site_name}</a> สำเร็จแล้ว
</p>

<p>
ไฟล์เหล่านี้สามารถดาวน์โหลดได้โดยใช้ลิงก์ต่อไปนี้ <a href="{transfer.download_link}">{transfer.download_link}</a>
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
         <tr>
             <td>ขนาด</td>
             <td>{ขนาด:transfer.size}</td>
         </tr>
         <tr>
             <td>ข้อมูลเพิ่มเติม</td>
             <td><a href="{transfer.link}">{transfer.link}</a></td>
         </tr>
     </tbody>
</ตาราง>

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>