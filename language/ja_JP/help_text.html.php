<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>ログイン</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>リストされているIdPのいずれかを選択し、あなたの所属機関のアカウントを使用してログインします。リストに所属機関がない場合や、ログインできない場合は、所属機関の管理者に相談してください。</li>
</ul>

<h3>ブラウザの機能</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled"/>転送ごとに最大{size:cfg:max_transfer_size}までのサイズのファイルをアップロードできます。</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled"/>それぞれ最大{size:cfg:max_legacy_file_size}のファイルを、転送ごとに{size:cfg:max_transfer_size}までアップロードできます。</li>
</ul>

<h3>HTML5での<i>任意サイズ</i>のアップロード</h3>
<ul class="fa-ul">
   <li><i class="fa-li fa fa-caret-right"></i>この方法は<img src="images/html5_installed.png" alt="HTML5 upload enabled"/>記号が上に表示されていたら利用できます。</li>
    <li><i class="fa-li fa fa-caret-right"></i>この機能を有効にするには、「ウェブ言語」の最新バージョンであるHTML5をサポートする最新のブラウザを使用してください。</li>
    <li><i class="fa-li fa fa-caret-right"></i>WindowsではFirefoxとChromeの最新のバージョン、Mac OS XおよびLinuxで動作します。</li>
    <li><i class="fa-li fa fa-caret-right"></i>
      中断またはキャンセルされたアップロードを<strong>再開</strong>できます。アップロードを再開するには、<strong>まったく同じファイルを再度送信</strong>してください。
      ファイルが以前と <strong>同じ名前とサイズ</strong>であることを確認してください。
      アップロードが開始すると、プログレスバーはアップロードが停止した場所にジャンプし、そこから続行します。
    </li>
</ul>

<h3>HTML5を使用せずにファイルごとに最大{size:cfg:max_legacy_file_size}をアップロードする</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>この方法には大きすぎるファイルをアップロードしようとすると、{cfg:site_name}は警告を表示します。</li>
    <li><i class="fa-li fa fa-caret-right"></i>この方法ではアップロードの再開はサポートされていません。</li>
</ul>

<h3>任意のサイズのダウンロード</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>最新のブラウザならどれでも問題なく動作し、ダウンロードに特に必要とされるものはありません。</li>
</ul>

<h3>サービス制約</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>受信者の上限: </strong>「,」または「;」で区切られた{cfg:max_transfer_recipients}個のメールアドレス</li>
   <li><i class="fa-li fa fa-caret-right"></i><strong>一度に転送できる最大ファイル数: </strong>{cfg:max_transfer_files}個</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>一度に転送できる最大サイズ: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>HTML5以外のブラウザのファイルあたりの最大ファイルサイズ: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>転送の有効期限: </strong>{cfg:default_transfer_days_valid}日(最大{cfg:max_transfer_days_valid}日)</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>ゲストの有効期限: </strong>{cfg:default_guest_days_valid}日(最大{cfg:max_guest_days_valid}日)</li>
</ul>

<h3>技術詳細</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong>は<a href="http://www.filesender.org/" target="_blank">FileSenderソフトウェア</a>を使用しています。
        FileSenderは、HTML5のアップロード方法が特定のブラウザでサポートされているかどうかを示します。
        これは主に、高度なブラウザ機能、特にHTML5のFileAPIが利用可能かどうかに依存します。
      主要なブラウザのHTML5のFileAPI対応状況については、次のページでご確認ください：<a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a>
        特に、<a href="http://caniuse.com/filereader" target="_blank">FileReader API </a>と<a href="http://caniuse.com/bloburls" target="_blank">Blob URLs </a>では、ブラウザが{size:cfg:max_legacy_file_size}より大きいアップロードをサポートするのに、ライトグリーンである(=サポートしている)必要があります。
       Opera 12はHTML5のFileAPIをサポートしているリストに入っていますが、FileSenderのHTML5によるアップロードに対応するために必要な機能をすべてサポートしているわけではないことにご注意ください。
    </li>
</ul>

<p>詳細については、次のURLをご覧ください：<a href="http://www.filesender.org/" target="_blank">www.filesender.org </a></p>