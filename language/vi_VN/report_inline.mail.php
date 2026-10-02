<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
chủ đề: Báo cáo về {target.type} #{target.id}

{thay thế:đồng bằng}

Thưa ông hoặc bà,

Đây là báo cáo về {target.type} của bạn:

{target.type} số : {target.id}

{if:target.type == "Chuyển khoản"}
Quá trình chuyển này có các tệp {transfer.files} với kích thước tổng thể là {size:transfer.size}.

Chuyển khoản này có sẵn cho đến {date:transfer.expires}.

Chuyển khoản này đã được gửi tới người nhận {transfer.recipients}.
{endif}
{if:target.type == "Tệp"}
Tệp này có tên là {file.path}, có kích thước là {size:file.size} và có sẵn cho đến {date:file.transfer.expires}.
{endif}
{if:target.type == "Người nhận"}
Người nhận này có địa chỉ email {recipient.email} và có giá trị đến {date:recipient.email}.
{endif}

Đây là nhật ký đầy đủ về những gì đã xảy ra với việc chuyển tiền:

{raw:content.plain}

Trân trọng,
{cfg:site_name}

{thay thế:html}

<p>
     Thưa ông hoặc bà,
</p>

<p>
     Đây là báo cáo về {target.type} của bạn:<br /><br />
    
     số {target.type} : {target.id}<br /><br />
    
     {if:target.type == "Chuyển khoản"}
     Quá trình chuyển này có các tệp {transfer.files} với kích thước tổng thể là {size:transfer.size}.<br /><br />
    
     Chuyển khoản này có sẵn cho đến {date:transfer.expires}.<br /><br />
    
     Chuyển khoản này đã được gửi tới người nhận {transfer.recipients}.
     {endif}
     {if:target.type == "Tệp"}
     Tệp này có tên là {file.path}, có kích thước là {size:file.size} và có sẵn cho đến {date:file.transfer.expires}.
     {endif}
     {if:target.type == "Người nhận"}
     Người nhận này có địa chỉ email {recipient.email} và có giá trị đến {date:recipient.email}.
     {endif}
</p>

<p>
     Đây là nhật ký đầy đủ về những gì đã xảy ra với việc chuyển tiền:
     <table class="auditlog" rules="rows">
         <thead>
             <th>Ngày</th>
             <th>Sự kiện</th>
             <th>Địa chỉ IP</th>
         </thead>
         <tbody>
             {raw:content.html}
         </tbody>
     </bảng>
</p>

<p>Trân trọng,<br/>
{cfg:site_name}</p>