<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: ดาวน์โหลดเสร็จแล้ว

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

การดาวน์โหลด {if:files>1}files{else}file{endif} ของคุณสิ้นสุดลงแล้ว :

{if:files>1}{each:files เป็นไฟล์}
   - {file.path} ({ขนาด:ไฟล์.ขนาด})
{endeach}{else}
{files.first().path} ({ขนาด:files.first().size})
{endif}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     การดาวน์โหลด {if:files>1}files{else}file{endif} ของคุณสิ้นสุดลงแล้ว :
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
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>