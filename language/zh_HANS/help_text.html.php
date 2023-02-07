<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>登录</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>通过选择列出的IdP并使用您所在机构的帐户登录。如果列表中没有所属机构或无法登录，请咨询所属机构的IT技术支持人员。</li>
</ul>

<h3>浏览器功能</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled"/>每次传输最多可以上传大小为{size:cfg:max_transfer_size}的文件。</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled"/>每次传输单个文件最大值为{size:cfg:max_legacy_file_size}，总共可以上传大小为{size:cfg:max_transfer_size}的文件。</li>
</ul>

<h3>上传<i>任意大小</i>在HTML5中</h3>
<ul class="fa-ul">
   <li><i class="fa-li fa fa-caret-right"></i>这个方法可以被使用如果
<img src="images/html5_installed.png" alt="HTML5 
upload enabled"/>符号显现在上面。</li>
    <li><i class="fa-li fa fa-caret-right"></i>要启用此功能，请使用支持最新版本HTML5的最新浏览器。</li>
    <li><i class="fa-li fa fa-caret-right"></i>已知最新版本的Firefox和Chrome在Windows，Mac OS X和Linux上都可以运行。</li>
    <li><i class="fa-li fa fa-caret-right"></i>
      可以<strong>恢复</strong>中断或取消的上传。要恢复上传，请<strong>再次发送完全相同的文件</strong>！
      请确认是否是<strong>相同的文件名称与大小</strong>，与之前一样。
      当上传开始时，进度条会跳转到停止上传的位置，然后继续。
    </li>
</ul>

<h3>在不使用HTML5的情况下，单个上传文件最大值为{size:cfg:max_legacy_file_size}</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>如果尝试上传太大的文件，{cfg:site_name}将显示警告。
</li>
    <li><i class="fa-li fa fa-caret-right"></i>此方法不支持恢复上传。</li>
</ul>

<h3>下载任意大小</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>最新的浏览器都可以正常运行，下载时没有特别的要求。</li>
</ul>

<h3>配置服务约束</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>收件人上限：
</strong>{cfg:max_transfer_recipients}的电子邮件被“，”以及“；”所区分</li>
   <li><i class="fa-li fa fa-caret-right"></i><strong>一次可传输的最大文件数：</strong>{cfg:max_transfer_files}个</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>一次可传输的最大文件大小： </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>非HTML5浏览器的每个文件的最大文件大小：</strong>
{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>传输过期
时间：</strong>{cfg:default_transfer_days_valid}天(最大
{cfg:max_transfer_days_valid}天)</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>访客过期
时间：</strong>{cfg:default_guest_days_valid}天(最大
{cfg:max_guest_days_valid}天)</li>
</ul>

<h3>技术细节</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong>使用<a 
href="http://www.filesender.org/" target="_blank">FileSender
软件</a>。
        FileSender指示特定浏览器是否支持HTML5的上载方法。
        这主要取决于高级浏览器功能，特别是HTML5的FileAPI是否可用。
        有关主要浏览器对于HTML5的FileAPI支持情况，请在以下页面查看：<a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a>
        特别是<a href="http://caniuse.com/filereader" 
target="_blank">FileReader API </a>和<a 
href="http://caniuse.com/bloburls" target="_blank">Blob URLs </a>
中，浏览器必须是浅绿色（=支持），以支持大于{size:cfg:max_legacy_file_size}的上传。
       请注意，尽管Opera12在支持HTML5的FileAPI列表中，但并不支持FileSender的HTML5上传所需的所有功能。
    </li>
</ul>

<p>想了解更多相关信息，请访问以下网址：<a 
href="http://www.filesender.org/" 
target="_blank">www.filesender.org </a></p>