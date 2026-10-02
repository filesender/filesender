<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Tệp{if:transfer.files>1}s{endif} đã được tải lên thành công

{thay thế:đồng bằng}

Thưa ông hoặc bà,

{if:transfer.files>1}tệp có{else}tệp đã{endif} sau đây đã được tải thành công lên {cfg:site_name}.

Có thể tải xuống các tệp này bằng liên kết sau: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files dưới dạng tệp}
   - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Thông tin thêm: {transfer.link}

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     {if:transfer.files>1}tệp có{else}tệp đã{endif} sau đây đã được tải thành công lên <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>

<p>
Có thể tải xuống các tệp này bằng liên kết sau <a href="{transfer.download_link}">{transfer.download_link}</a>
</p>

<quy tắc bảng="hàng">
     <thead>
         <tr>
             <th colspan="2">Chi tiết giao dịch</th>
         </tr>
     </thead>
     <tbody>
         <tr>
             <td>Tệp{if:transfer.files>1}s{endif}</td>
             <td>
                 {if:transfer.files>1}
                 <ul>
                     {mỗi:transfer.files dưới dạng tệp}
                         <li>{file.path} ({size:file.size})</li>
                     {endeach}
                 </ul>
                 {khác}
                 {transfer.files.first().path} ({size:transfer.files.first().size})
                 {endif}
             </td>
         </tr>
         <tr>
             <td>Kích thước</td>
             <td>{size:transfer.size}</td>
         </tr>
         <tr>
             <td>Thông tin thêm</td>
             <td><a href="{transfer.link}">{transfer.link}</a></td>
         </tr>
     </tbody>
</bảng>

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>