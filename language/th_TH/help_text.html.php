<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>เข้าสู่ระบบ</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>คุณเข้าสู่ระบบผ่านหนึ่งในผู้ให้บริการข้อมูลประจำตัวที่ระบุไว้โดยใช้บัญชีสถาบันมาตรฐานของคุณ หากคุณไม่เห็นสถาบันของคุณในรายการ หรือการเข้าสู่ระบบของคุณล้มเหลว โปรดติดต่อฝ่ายสนับสนุนด้านไอทีในพื้นที่ของคุณ</li>
</ul>

<h3>คุณสมบัติของเบราว์เซอร์ของคุณ</h3>
<ul class="fa-ul">
     <li data-feature="html5"><img src="images/html5_installed.png" alt="เปิดใช้งานการอัปโหลด HTML5" /> คุณสามารถอัปโหลดไฟล์ขนาดใดก็ได้สูงสุด {size:cfg:max_transfer_size} ต่อการถ่ายโอน< /li>
     <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 อัปโหลดถูกปิดใช้งาน" /> คุณสามารถอัปโหลดไฟล์ได้สูงสุดไฟล์ละ {size:cfg:max_legacy_file_size} และไม่เกิน {ขนาด :cfg:max_transfer_size} ต่อการโอน</li>
</ul>

<h3>อัปโหลด <i>ขนาดใดก็ได้</i> ด้วย HTML5</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>คุณจะใช้วิธีนี้ได้หากอัปโหลด <img src="images/html5_installed.png" alt="HTML5 เปิดใช้งาน" /> เครื่องหมายแสดงด้านบน</li>
     <li><i class="fa-li fa fa-caret-right"></i>หากต้องการเปิดใช้งานฟังก์ชันนี้ เพียงใช้เบราว์เซอร์รุ่นล่าสุดที่รองรับ HTML5 ซึ่งเป็น "ภาษาของเว็บ" เวอร์ชันล่าสุด </li>
     <li><i class="fa-li fa fa-caret-right"></i>ทราบว่า Firefox และ Chrome เวอร์ชันล่าสุดบน Windows, Mac OS X และ Linux ใช้งานได้</li>
     <li><i class="fa-li fa fa-caret-right"></i>
         คุณสามารถ<strong>ดำเนินการต่อ</strong>การอัปโหลดที่ถูกขัดจังหวะหรือถูกยกเลิก หากต้องการดำเนินการอัปโหลดต่อ เพียง <strong>ส่งไฟล์เดิม</strong> อีกครั้ง !
         ตรวจสอบว่าไฟล์มี <strong>ชื่อและขนาดเดียวกัน</strong> เหมือนเมื่อก่อน
         เมื่อการอัปโหลดของคุณเริ่มขึ้น คุณควรสังเกตว่าแถบความคืบหน้าข้ามไปยังจุดที่หยุดการอัปโหลด และดำเนินการต่อจากที่นั่น
     </li>
</ul>

<h3>อัปโหลดได้สูงสุด {size:cfg:max_legacy_file_size} ต่อไฟล์โดยไม่ใช้ HTML5</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} จะเตือนคุณว่าคุณควรพยายามอัปโหลดไฟล์ที่ใหญ่เกินไปสำหรับวิธีนี้</li>
     <li><i class="fa-li fa fa-caret-right"></i>วิธีนี้ไม่สนับสนุนการอัปโหลดต่อ</li>
</ul>

<h3>ดาวน์โหลดได้ทุกขนาด</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>เบราว์เซอร์รุ่นใหม่ๆ ก็ทำได้ดี ไม่จำเป็นต้องดาวน์โหลดอะไรเป็นพิเศษ</li>
</ul>

<h3>กำหนดข้อจำกัดของบริการ</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i><strong>จำนวนผู้รับสูงสุด : </strong>{cfg:max_transfer_recipients} ที่อยู่อีเมลคั่นด้วยเครื่องหมายจุลภาคหรือเครื่องหมายอัฒภาค </li>
     <li><i class="fa-li fa fa-caret-right"></i><strong>จำนวนไฟล์สูงสุดต่อการถ่ายโอน : </strong>{cfg:max_transfer_files}</li>
     <li><i class="fa-li fa fa-caret-right"></i><strong>ขนาดสูงสุดต่อการถ่ายโอน : </strong>{size:cfg:max_transfer_size}</li>
     <li><i class="fa-li fa fa-caret-right"></i><strong>ขนาดไฟล์สูงสุดต่อไฟล์สำหรับเบราว์เซอร์ที่ไม่ใช่ HTML5 : </strong>{size:cfg:max_legacy_file_size}</ ลี่>
     <li><i class="fa-li fa fa-caret-right"></i><strong>วันหมดอายุของการโอนย้าย : </strong>{cfg:default_transfer_days_valid} (สูงสุด {cfg:max_transfer_days_valid})</ ลี่>
     <li><i class="fa-li fa fa-caret-right"></i><strong>วันหมดอายุของแขก : </strong>{cfg:default_guest_days_valid} (สูงสุด {cfg:max_guest_days_valid})</ ลี่>
</ul>

<h3>รายละเอียดทางเทคนิค</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>
         <strong>{cfg:site_name}</strong> ใช้ <a href="http://www.filesender.org/" target="_blank">ซอฟต์แวร์ FileSender</a>
         FileSender ระบุว่าวิธีการอัปโหลด HTML5 ได้รับการสนับสนุนสำหรับเบราว์เซอร์เฉพาะหรือไม่
         ทั้งนี้ขึ้นอยู่กับความพร้อมใช้งานของฟังก์ชันขั้นสูงของเบราว์เซอร์ โดยเฉพาะ HTML5 FileAPI
         โปรดใช้เว็บไซต์ <a href="http://caniuse.com/fileapi" target="_blank">"เมื่อใดที่ฉันสามารถใช้..."</a> เพื่อติดตามความคืบหน้าของการติดตั้ง HTML5 FileAPI สำหรับเบราว์เซอร์หลักทั้งหมด .
         โดยเฉพาะการสนับสนุน <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> และ <a href="http://caniuse.com/bloburls" target=" _blank">Blob URL</a> ต้องเป็นสีเขียวอ่อน (=รองรับ) เพื่อให้เบราว์เซอร์รองรับการอัปโหลดที่มีขนาดใหญ่กว่า {size:cfg:max_legacy_file_size}
         โปรดทราบว่าแม้ว่า Opera 12 จะแสดงรายการว่ารองรับ HTML5 FileAPI แต่ปัจจุบันยังไม่รองรับสิ่งที่จำเป็นทั้งหมดเพื่อรองรับการใช้วิธีการอัปโหลด HTML5 ใน FileSender
     </li>
</ul>

<p>สำหรับข้อมูลเพิ่มเติม โปรดไปที่ <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>