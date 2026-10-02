<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>{cfg:site_name}へようこそ</h1>
<p>
    このサービスが機能するために、ファイル、ファイルにアクセスできるユーザー、および、何が起こったかに関する情報を保持する必要があります。ファイルは有効期限が切れるとシステムから自動的に削除され、その他の保持されている情報は、一定の時間が経過するとシステムとデータベースから削除されます。このページでは、このサイトがさまざまな情報を保持する期間を確認することができます。
</p>
<p>
    転送が削除されると、その転送に関連して送信されたメールのコピーとともに、すべての関連ファイルも削除されることに注意してください。
</p>
<?php 
if(ShredFile::shouldUseShredFile()){
    echo "<p>このサイトは、アップロードされたファイルが削除されたときに、それらを細断処理するように設定されています。"; 
    echo "ファイルの細断処理の一部として、システムからユーザーデータを本当に削除するために、"; 
    echo "ディスク上の同じ場所に何度もデータを書き込みます。"; 
    echo "これにより、このサービスのユーザーには追加的なプライバシーが提供されています。</p>"; 
}
?>
