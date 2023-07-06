<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: ดาวน์โหลดใบเสร็จรับเงิน

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

{if:files>1}หลายไฟล์{else}ไฟล์หนึ่ง{endif} ที่คุณอัปโหลด {if:files>1}ดาวน์โหลด{else}แล้ว{endif} จาก {cfg:site_name} โดย {recipient.email} :

{if:files>1}{each:files เป็นไฟล์}
   - {file.path} ({ขนาด:ไฟล์.ขนาด})
{endeach}{else}
{files.first().path} ({ขนาด:files.first().size})
{endif}

คุณสามารถเข้าถึงไฟล์ของคุณและดูสถิติการดาวน์โหลดโดยละเอียดในหน้าการถ่ายโอนที่ {files.first().transfer.link}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     {if:files>1}หลายไฟล์{else}ไฟล์หนึ่ง{endif} ที่คุณอัปโหลด {if:files>1}ดาวน์โหลด{else}แล้ว{endif} จาก {cfg:site_name} โดย {recipient.email}
</p>

<p>
     {หาก:ไฟล์>1}
     <ul>
         {แต่ละไฟล์เป็นไฟล์}
             <li>{file.path} ({ขนาด:file.size})</li>
         {จบ}
     </ul>
     {อื่น}
     {files.first().path} ({ขนาด:files.first().size})
     {endif}
</p>

<p>
     คุณสามารถเข้าถึงไฟล์ของคุณและดูสถิติการดาวน์โหลดโดยละเอียดในหน้าการถ่ายโอนที่ <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>
</p>

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>