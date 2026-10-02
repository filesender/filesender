<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Hoşgeldiniz {cfg:site_name}</h1>
<p>
    Bu servisin çalışması için dosyalar, onlara kimlerin ulaşabileceği ve nelerin 
    olduğuna dair bazı bilgileri tutması gerekir. Dosyalar, zaman aşımın uğradığında otomatik olarak sistemden kaldırılacak ve tutulan diğer bilgiler belirli bir süre geçtikten sonra sistem ve veri tabanından kaldırılacaktır. 
    Bu sayfa, farklı bilgi parçalarının bu kurulumca ne kadar süreyle tutulduğunu görmenize olanak tanır.
</p>
<p>
    Bir aktarım silindiğinde, bu aktarımla bağlantı olarak gönderilmiş olan tüm e-postaların kopyaları ile birlikte ilgili tüm dosyaların da silindiğine dikkat edin.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Bu site, silindiklerinde yüklenen dosyaları parçalayacak şekilde yapılandırılmıştır. ";
    echo "Bir dosyanın parçalanması, diskteki aynı konum üzerine veri yazmayı da içerir";
    echo " Kullanıcı verilerini gerçekten sistemden kaldırmak için pek çok kez. ";
    echo "Bu da bu servisin kullanıcıları için daha fazla gizlilik sağlar.</p>";
}
?>
