<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Khách bắt đầu tải tệp lên

{thay thế:đồng bằng}

Thưa ông hoặc bà,

Vị khách sau bắt đầu tải tệp lên từ phiếu thưởng của bạn :

Khách: {guest.email}
Liên kết phiếu thưởng: {cfg:site_url}?s=upload&vid={guest.token}

Phiếu thưởng có sẵn cho đến {date:guest.expires} sau thời gian đó, phiếu thưởng sẽ tự động bị xóa.

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     Vị khách sau bắt đầu tải tệp lên từ phiếu thưởng của bạn :
</p>

<quy tắc bảng="hàng">
     <thead>
         <tr>
             <th colspan="2">Chi tiết phiếu thưởng</th>
         </tr>
     </thead>
     <tbody>
         <tr>
             <td>Khách</td>
             <td><a href="mailto:{guest.email}">{guest.email}</a></td>
         </tr>
         <tr>
             <td>Liên kết phiếu thưởng</td>
             <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
         </tr>
         <tr>
             <td>Có hiệu lực đến</td>
             <td>{date:guest.expires}</td>
         </tr>
     </tbody>
</bảng>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>