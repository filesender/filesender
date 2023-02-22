<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>登錄</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>通過選擇列出的IdP並使用您所在機構的帳戶登錄。如果列表中沒有所屬機構或無法登錄，請諮詢所屬機構的IT技術支持人員。 </li>
</ul>

<h3>瀏覽器功能</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled"/>每次傳輸最多可以上傳大小為{size:cfg:max_transfer_size}的文件。 </li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled"/>每次傳輸單個文件最大值為{size:cfg:max_legacy_file_size}，總共可以上傳大小為{size:cfg:max_transfer_size}的文件。 </li>
</ul>

<h3>上傳<i>任意大小</i>在HTML5中</h3>
<ul class="fa-ul">
   <li><i class="fa-li fa fa-caret-right"></i>這個方法可以被使用如果
<img src="images/html5_installed.png" alt="HTML5 
upload enabled"/>符號顯現在上面。</li>
    <li><i class="fa-li fa fa-caret-right"></i>要啟用此功能，請使用支持最新版本HTML5的最新瀏覽器。 </li>
    <li><i class="fa-li fa fa-caret-right"></i>已知最新版本的Firefox和Chrome在Windows，Mac OS X和Linux上都可以運行。 </li>
    <li><i class="fa-li fa fa-caret-right"></i>
      可以<strong>恢復</strong>中斷或取消的上傳。要恢復上傳，請<strong>再次發送完全相同的文件</strong>！
      請確認是否是<strong>相同的文件名稱與大小</strong>，與之前一樣。
      當上傳開始時，進度條會跳轉到停止上傳的位置，然後繼續。
    </li>
</ul>

<h3>在不使用HTML5的情況下，單個上傳文件最大值為{size:cfg:max_legacy_file_size}</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>如果嘗試上傳太大的文件，{cfg:site_name}將顯示警告。
</li>
    <li><i class="fa-li fa fa-caret-right"></i>此方法不支持恢復上傳。 </li>
</ul>

<h3>下載任意大小</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>最新的瀏覽器都可以正常運行，下載時沒有特別的要求。 </li>
</ul>

<h3>配置服務約束</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>收件人上限：
</strong>{cfg:max_transfer_recipients}的電子郵件被“，”以及“；”所區分</li>
   <li><i class="fa-li fa fa-caret-right"></i><strong>一次可傳輸的最大文件數：</strong>{cfg:max_transfer_files}個</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>一次可傳輸的最大文件大小： </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>非HTML5瀏覽器的每個文件的最大文件大小：</strong>
{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>傳輸過期
時間：</strong>{cfg:default_transfer_days_valid}天(最大
{cfg:max_transfer_days_valid}天)</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>訪客過期
時間：</strong>{cfg:default_guest_days_valid}天(最大
{cfg:max_guest_days_valid}天)</li>
</ul>

<h3>技術細節</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong>使用<a 
href="http://www.filesender.org/" target="_blank">FileSender
軟件</a>。
        FileSender指示特定瀏覽器是否支持HTML5的上載方法。
        這主要取決於高級瀏覽器功能，特別是HTML5的FileAPI是否可用。
        有關主要瀏覽器對於HTML5的FileAPI支持情況，請在以下頁面查看：<a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a>
        特別是<a href="http://caniuse.com/filereader" 
target="_blank">FileReader API </a>和<a 
href="http://caniuse.com/bloburls" target="_blank">Blob URLs </a>
中，瀏覽器必須是淺綠色（=支持），以支持大於{size:cfg:max_legacy_file_size}的上傳。
       請注意，儘管Opera12在支持HTML5的FileAPI列表中，但並不支持FileSender的HTML5上傳所需的所有功能。
    </li>
</ul>

<p>想了解更多相關信息，請訪問以下網址：<a 
href="http://www.filesender.org/" 
target="_blank">www.filesender.org </a></p>