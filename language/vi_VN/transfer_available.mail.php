<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Tệp{if:transfer.files>1}s{endif} có sẵn để tải xuống
chủ đề: {chuyển.subject}

{thay thế:đồng bằng}

Thưa ông hoặc bà,

{if:transfer.files>1}tệp có{else}tệp đã{endif} sau đây đã được {transfer.user_email} tải lên {cfg:site_name} và bạn đã được cấp quyền tải xuống {if:transfer.files> 1}nội dung{else}its{endif} của chúng:

{if:transfer.files>1}{each:transfer.files dưới dạng tệp}
   - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Liên kết tải xuống: {recipient.download_link}

Giao dịch có sẵn cho đến {date:transfer.expires} sau thời gian đó, giao dịch sẽ tự động bị xóa.

{if:transfer.message || chuyển.subject}
Tin nhắn cá nhân từ {transfer.user_email}: {transfer.subject}

{chuyển.message}
{endif}

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     {if:transfer.files>1}tệp có{else}tệp đã{endif} sau đây đã được tải lên <a href="{cfg:site_url}">{cfg:site_name}</a> bởi <a href= "mailto:{transfer.user_email}">{transfer.user_email}</a> và bạn đã được cấp quyền tải xuống nội dung {if:transfer.files>1}của họ{else}của họ{endif}.
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
         {if:transfer.files>1}
         <tr>
             <td>Kích thước chuyển</td>
             <td>{size:transfer.size}</td>
         </tr>
         {endif}
         <tr>
             <td>Ngày hết hạn</td>
             <td>{date:transfer.expires}</td>
         </tr>
         <tr>
             <td>Liên kết tải xuống</td>
             <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
         </tr>
     </tbody>
</bảng>

{if:transfer.message}
<p>
     Tin nhắn cá nhân từ {transfer.user_email}:
</p>
<p class="message">
     {chuyển.message}
</p>
{endif}

<p>
     Trân trọng,<br />
     {cfg:site_name}
</p>