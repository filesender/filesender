<h3>Přihlášení</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Přihlásíte se pomocí zveřejněných poskytovatelů identity pomocí svého běžného účtu ve vybrané organizaci. Pokud není Vaše organizace uvedena v seznamu nebo se nemůžete přihlásit, kontaktujte svou místní správu IT. </li>
</ul>

<h3>Your browser's features</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload povolen" /> Můžete nahrávat soubory až do velikosti {size:cfg:max_transfer_size} na jeden přenos.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload zakázán" /> Můžete nahrávat soubory o velikosti maximálně {size:cfg:max_legacy_file_size} každý soubor, celkem maximálně {size:cfg:max_transfer_size} na celý přenos.</li>
</ul>

<h3>Nahrávání <i>jakékoliv velikosti</i> pomocí HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Můžete použít tuto metodu, pokud výše vidíte tuto ikonu: <img src="images/html5_installed.png" alt="HTML5 upload enabled" /></li>
    <li><i class="fa-li fa fa-caret-right"></i>K využití této funkčnosti stačí použít jakýkoliv aktuální prohlížeč s podporou HTML5, poslední verze tohoto "webového jazyka".</li>
    <li><i class="fa-li fa fa-caret-right"></i>Aktuální verze prohlížečů Firefox, Chrome na Windows, Mac OS X a Linuxu fungují.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Můžete <strong>navázat</strong> přerušené nebo pozastavené nahrávání. Pro navázání přenosu prostě pošlete <strong>stejné soubory</strong> znovu!
        Ubezpečte se, že soubory mají <strong>stejná jména a velikosti</strong> jako předtím.
        Jakmile bude nahrávání spuštěno, povšimnete si, že nahrávání pokračuje v místě, kde bylo přerušeno.
    </li>
</ul>

<h3>Nahrávání až do velikosti jednoho souboru {size:cfg:max_legacy_file_size} bez HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>FileSender Vás upozorní, pokud se pokusíte nahrát soubor větší, než je povoleno.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Navázání přerušených přenosů není pomocí této metody dostupné.</li>
</ul>

<h3>Stahování souborů jakékoliv velikosti</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Jakýkoliv aktuální prohlížeč bude fungovat, není vyžadována speciální funkcionalita</li>
</ul>

<h3>Lokální nastavení této instalace</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximální počet příjemců: </strong>{cfg:max_transfer_recipients} emailůvých adres oddělených čárkou či středníkem</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximální počet souborů v jednom přenosu: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximální velikost jednoho přenosu : </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maximální velikost souboru pro prohlížeče bez podpory HTML5: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Životnost přenosu: </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Životnost pozvánky pro hosta: </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Technické detaily</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> používá <a href="http://www.filesender.org/" target="_blank">FileSender software</a>.
        FileSender indikuje, zda je k dispozici metoda přenosu HTML5 pro konkrétní prohlížeč.
        Závisí ho hlavně na podpoře pokročilých vlastností, konkrétně HTML5 FileAPI.
        Na adrese <a href="http://caniuse.com/fileapi" target="_blank">"Kdy mohu použít..."</a> můžete zkontrolovat stav implementace pro jednotlivé prohlížeče.
        Konkrétně podporu pro <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> a <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> musí být světle zelené (=podporováno), aby prohlížeč podporoval nahrávání větší než {size:cfg:max_legacy_file_size}.
        Navzdory tomu, že Opera 12 uvádí podporu HTML5 FileAPI, momentálně nepodporuje vše, co je třeba k využití HTML5 nahrávací metody ve FileSenderu.
    </li>
</ul>

<p>Pro více informací navštivte <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
