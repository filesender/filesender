<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Pieteikšanās</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Jūs piesakāties, izmantojot kādu no sarakstā norādītajiem identitātes nodrošinātājiem, izmantojot savu standarta institucionālo kontu. Ja sarakstā neredzat savu iestādi vai pieteikšanās neizdodas, lūdzu, sazinieties ar savas institūcijas IT atbalsta komandu</li>
</ul>

<h3>Jūsu pārlūkprogrammas iespējas</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> Vienā pārsūtīšanā varat augšupielādēt jebkura lieluma failus, kuru kopējais izmērs summā nepārsniedz {size:cfg:max_transfer_size}.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled" /> Vienā pārsūtīšanā varat augšupielādēt failus, kur katrs nav lielāks par {size:cfg:max_legacy_file_size} un kuru kopējais izmērs summā nepārsniedz {size:cfg:max_transfer_size}.</li>
</ul>

<h3>Augšupielādes <i>jebkurā izmērā</i> ar HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Šo metodi Jūs varēsiet izmantot, ja <img src="images/html5_installed.png" alt="HTML5 upload enabled" /> zīme ir attēlota augstāk</li>
    <li><i class="fa-li fa fa-caret-right"></i>Lai iespējotu šo funkcionalitāti, izmantojiet atjauninātu pārlūkprogrammu, kas atbalsta HTML5, jaunāko versiju no "tīmekļa valoda".</li>
    <li><i class="fa-li fa fa-caret-right"></i>Zināms, ka jaunākās Firefox un Chrome versijas operētājsistēmās Windows, Mac OS X un Linux darbojas.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Varat <strong>atsākt</strong> pārtrauktu vai atceltu augšupielādi. Lai atsāktu augšupielādi, vienkārši <strong>nosūtiet tieši tos pašus failus</strong> vēlreiz !
        Pārliecinieties, ka failiem ir tādi <strong>paši nosaukumi un izmēri</strong> kā iepriekš.
        Kad augšupielāde tiek sākta, pievērsiet uzmanību augšupielādes joslas pārlēcienu uz vietu, kur augšupielāde tika apturēta, un turpiniet no turienes.
    </li>
</ul>

<h3>Augšupielāde {size:cfg:max_legacy_file_size} vienā failā bez HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} brīdinās, ja mēģināsit augšupielādēt failu, kas ir pārāk liels šai metodei.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Šī metode neatbalsta augšupielāžu atsākšanu.</li>
</ul>

<h3>Jebkura izmēra lejupielādes</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Jebkura moderna pārlūkprogramma darbosies labi, nekas speciāls lejupielādēm nav nepieciešams</li>
</ul>

<h3>Konfigurētie pakalpojuma ierobežojumi</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimālais adresātu skaits : </strong>{cfg:max_transfer_recipients} e-pasta adreses, kas atdalītas ar komatu vai semikolu</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimālais failu skaits vienā pārsūtīšanā : </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimālais pārsūtīšanas kopējais izmērs : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksimālais faila izmērs vienā failā pārlūkprogrammās, kas nav HTML5 : </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Pārsūtīšanas derīguma termiņš : </strong>{cfg:default_transfer_days_valid} (maks. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Viesu derīguma termiņš : </strong>{cfg:default_guest_days_valid} (maks. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Tehniskā informācija</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> izmanto <a href="http://www.filesender.org/" target="_blank">FileSender programmatūru</a>.
        FileSender norāda, vai HTML5 augšupielādes metode tiek atbalstīta noteiktajā pārlūkprogrammā.
        Tas galvenokārt atkarīgs no uzlabotas pārlūkprogrammas funkcionalitātes pieejamības, jo īpaši HTML5 FileAPI.
        Lūdzu, izmantojiet <a href="http://caniuse.com/fileapi" target="_blank">"Kad es varu izmantot..."</a> tīmekļvietni, lai pārraudzītu HTML5 FileAPI ieviešanas progresu visām galvenajām pārlūkprogrammām.
        Jo īpaši atbalstām <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> un <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> jābūt gaiši zaļam (=atbalstīts), lai pārlūkprogramma atbalstītu augšupielādes, kas lielākas par  {size:cfg:max_legacy_file_size}.
        Lūdzu, ņemiet vērā, ka, lai gan Opera 12 atbalsta HTML5 FileAPI, tā pašlaik neatbalsta visu, kas nepieciešams HTML5 augšupielādes metodes izmantošanai iekš FileSender.
    </li>
</ul>

<p>Lai iegūtu papildinformāciju, apmeklējiet vietni  <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>