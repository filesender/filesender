<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>로그인</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>나열된 아이디 제공자 중 하나를 선택하고 기관 계정을 통해 로그인할 수 있습니다.  소속기관을 목록에서 찾을 수 없으면 소속기관의 IT 관리부서에 문의하십시요</li>
</ul>

<h3>귀하의 브라우져 특성</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> 전송 당 {size:cfg:max_transfer_size} 크기의 파일까지 업로드 할 수 있습니다.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled" /> 최대 {size:cfg:max_legacy_file_size} 및 전송당 최대 {size:cfg:max_transfer_size}의 파일을 업로드할 수 있습니다.</li>
</ul>

<h3>HTML5를 이용한 <i>모든 크기</i>의 파일 업로드</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>위에 <img src="images/html5_installed.png" alt="HTML5 upload enabled" /> 표시가 되어 있으면 본 방식을 사용할 수 있습니다.</li>
    <li><i class="fa-li fa fa-caret-right"></i>이 기능을 사용하려면 HTML5가 지원되는 브라우져를 이용하십시요.</li>
    <li><i class="fa-li fa fa-caret-right"></i>최신 버전의 Firefox, Chrome이 HTML5를 지원합니다.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        중단되거나 취소된 업로드를 <strong>재개</strong>할 수 있습니다. 업로드를 재개하려면 <strong>업로드했던 파일과 동일한 파일</strong>을  전송하십시요!
        파일이 이전과 <strong>동일한 이름 및 크기</strong>인지 확인하세요.
        업로드가 시작되면 진행률 표시줄이 업로드가 중단된 위치로 이동하고 거기서부터 계속 진행합니다.
    </li>
</ul>

<h3>HTML5가 지원되지 않으면 {size:cfg:max_legacy_file_size}까지 업로드</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name}가 업로드할 수 있는 파일 크기의 초과에 대해서 경고할 겁니다.</li>
    <li><i class="fa-li fa fa-caret-right"></i>이 방식은 업로드를 재개할 수 없습니다.</li>
</ul>

<h3>크기에 관계없이 다운로드</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>다운로드는 파일 크기에 제한을 받지 않습니다</li>
</ul>

<h3>설정된 제한 사항</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>최대 수신자 수: </strong>{cfg:max_transfer_recipients} 이메일 주소를 코마나 세미콜론으로 분리</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>전송 당 최대 파일 수 : </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>전송 당 최대 크기 : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>HTML5를 지원하지 않는 경우 최대 파일 크기 : </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>전송 만료 일 : </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>게스트 만료일 : </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul>

<h3>기술적 세부 사항</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong>는 <a href="http://www.filesender.org/" target="_blank">FileSender software를 이용합니다</a>.
        FileSender는 특정 브라우져에서 HTML5 업로드 방식을 지원하는지 표시합니다.
       지원 여부는 브라우져의 기능, 특히 HTML5 FileAPI의 가용성에 달려 있습니다.
        주요 브라우저에 대한 HTML5 FileAPI의 구현 진행 상황을 모니터링하려면 <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> 웹사이트를 방문하세요. 
        특히 <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a>와 <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a>는 {size:cfg:max_legacy_file_size} 크기 이상의 업로드를 위해 필요합니다.
        Opera 12가 HTML5 FileAPI를 지원하는 것으로 표시되어 있지만 현재 FileSender에서 HTML5 업로드를 위해 요구되는 기능을 지원하지는 않으므로 참고하시기 바랍니다.
    </li>
</ul>

<p><a href="http://www.filesender.org/" target="_blank">www.filesender.org</a>에서 자세한 사항을 확인하실 수 있습니다.</p>