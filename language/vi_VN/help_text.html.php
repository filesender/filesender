<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Đăng nhập</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>Bạn đăng nhập thông qua một trong những Nhà cung cấp danh tính được liệt kê bằng tài khoản tổ chức tiêu chuẩn của mình. Nếu bạn không thấy tổ chức của mình trong danh sách hoặc đăng nhập không thành công, vui lòng liên hệ với bộ phận hỗ trợ CNTT tại địa phương</li>
</ul>

<h3>Các tính năng của trình duyệt của bạn</h3>
<ul class="fa-ul">
     <li data-feature="html5"><img src="images/html5_install.png" alt="HTML5 upload enable" /> Bạn có thể tải các tệp có kích thước bất kỳ lên {size:cfg:max_transfer_size} mỗi lần chuyển.< /li>
     <li data-feature="nohtml5"><img src="images/html5_none.png" alt="Tải lên HTML5 bị vô hiệu hóa" /> Bạn có thể tải lên mỗi tệp có kích thước tối đa là {size:cfg:max_legacy_file_size} và tối đa là {size :cfg:max_transfer_size} mỗi lần chuyển.</li>
</ul>

<h3>Tải lên ở <i>bất kỳ kích thước nào</i> với HTML5</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>Bạn sẽ có thể sử dụng phương pháp này nếu <img src="images/html5_install.png" alt="HTML5 tải lên đã bật" /> ký hiệu được hiển thị ở trên</li>
     <li><i class="fa-li fa fa-caret-right"></i>Để kích hoạt chức năng này, chỉ cần sử dụng trình duyệt cập nhật hỗ trợ HTML5, phiên bản mới nhất của "ngôn ngữ web". </li>
     <li><i class="fa-li fa fa-caret-right"></i>Các phiên bản cập nhật của Firefox và Chrome trên Windows, Mac OS X và Linux được biết là hoạt động.</li>
     <li><i class="fa-li fa fa-caret-right"></i>
         Bạn có thể <strong>tiếp tục</strong> tải lên bị gián đoạn hoặc bị hủy. Để tiếp tục tải lên, chỉ cần <strong>gửi lại chính các tệp đó</strong>!
         Đảm bảo các tệp có <strong>cùng tên và kích thước</strong> như trước đây.
         Khi quá trình tải lên của bạn bắt đầu, bạn sẽ thấy thanh tiến trình chuyển đến nơi quá trình tải lên bị tạm dừng và tiếp tục từ đó.
     </li>
</ul>

<h3>Tải lên tối đa {size:cfg:max_legacy_file_size} mỗi tệp không có HTML5</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} sẽ cảnh báo bạn nếu bạn cố tải lên một tệp quá lớn đối với phương thức này.</li>
     <li><i class="fa-li fa fa-caret-right"></i>Phương pháp này không hỗ trợ tiếp tục tải lên.</li>
</ul>

<h3>Tải xuống ở mọi kích thước</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>Mọi trình duyệt hiện đại đều hoạt động tốt, không yêu cầu gì đặc biệt để tải xuống</li>
</ul>

<h3>Các ràng buộc dịch vụ đã định cấu hình</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i><strong>Số lượng người nhận tối đa : </strong>{cfg:max_transfer_recipients} địa chỉ email được phân tách bằng dấu phẩy hoặc dấu chấm phẩy </li>
     <li><i class="fa-li fa fa-caret-right"></i><strong>Số lượng tệp tối đa cho mỗi lần truyền: </strong>{cfg:max_transfer_files}</li>
     <li><i class="fa-li fa fa-caret-right"></i><strong>Kích thước tối đa cho mỗi lần chuyển: </strong>{size:cfg:max_transfer_size}</li>
     <li><i class="fa-li fa fa-caret-right"></i><strong>Kích thước tệp tối đa trên mỗi tệp cho các trình duyệt không phải HTML5 : </strong>{size:cfg:max_legacy_file_size}</strong> li>
     <li><i class="fa-li fa fa-caret-right"></i><strong>Ngày hết hạn chuyển: </strong>{cfg:default_transfer_days_valid} (tối đa {cfg:max_transfer_days_valid})</ li>
     <li><i class="fa-li fa fa-caret-right"></i><strong>Ngày hết hạn của khách : </strong>{cfg:default_guest_days_valid} (tối đa {cfg:max_guest_days_valid})</ li>
</ul>

<h3>Chi tiết kỹ thuật</h3>
<ul class="fa-ul">
     <li><i class="fa-li fa fa-caret-right"></i>
         <strong>{cfg:site_name}</strong> sử dụng <a href="http://www.filesender.org/" target="_blank">phần mềm FileSender</a>.
         FileSender cho biết phương thức tải lên HTML5 có được hỗ trợ cho một trình duyệt cụ thể hay không.
         Điều này phụ thuộc chủ yếu vào tính khả dụng của chức năng trình duyệt nâng cao, cụ thể là HTML5 FileAPI.
         Vui lòng sử dụng trang web <a href="http://caniuse.com/fileapi" target="_blank">"Khi nào tôi có thể sử dụng..."</a> để theo dõi tiến độ triển khai của HTML5 FileAPI cho tất cả các trình duyệt chính .
         Đặc biệt hỗ trợ cho <a href="http://caniuse.com/filereader" target="_blank">API FileReader</a> và <a href="http://caniuse.com/bloburls" target=" _blank">URL Blob</a> cần phải có màu xanh lục nhạt (=supported) để trình duyệt hỗ trợ các tệp tải lên lớn hơn {size:cfg:max_legacy_file_size}.
         Xin lưu ý rằng mặc dù Opera 12 được liệt kê để hỗ trợ HTML5 FileAPI, nhưng nó hiện không hỗ trợ tất cả những gì cần thiết để hỗ trợ sử dụng phương thức tải lên HTML5 trong FileSender.
     </li>
</ul>

<p>Để biết thêm thông tin, vui lòng truy cập <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>