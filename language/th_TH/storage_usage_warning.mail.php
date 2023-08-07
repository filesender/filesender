<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
เรื่อง: คำเตือนการใช้พื้นที่เก็บข้อมูล

{ทางเลือก:ธรรมดา}

ถึงคุณหรือคุณนาย,

คำเตือนการใช้พื้นที่เก็บข้อมูลของ {cfg:site_name} :

{แต่ละ:คำเตือนเป็นคำเตือน}
   - {warning.filesystem} ({size:warning.total_space}) เหลือเพียง {size:warning.free_space} ({warning.free_space_pct}%)
{จบ}

คุณสามารถดูรายละเอียดเพิ่มเติมได้ที่ {cfg:site_url}

ขอแสดงความนับถืออย่างสูง,
{cfg:site_name}

{ทางเลือก:html}

<p>
     ถึงคุณหรือคุณนาย,
</p>

<p>
     คำเตือนการใช้พื้นที่เก็บข้อมูลของ {cfg:site_name} :
</p>

<ul>
{แต่ละ:คำเตือนเป็นคำเตือน}
     <li>{warning.filesystem} ({size:warning.total_space}) เหลือเพียง {size:warning.free_space} ({warning.free_space_pct}%)</li>
{จบ}
</ul>

<p>
     คุณสามารถดูรายละเอียดเพิ่มเติมได้ที่ <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
     ขอแสดงความนับถือ<br />
     {cfg:site_name}
</p>