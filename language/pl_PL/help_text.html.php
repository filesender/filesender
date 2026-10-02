<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Witamy w usłudze FileSender</h3>

<p>
    FileSender to aplikacja internetowa, która pozwala uwierzytelnionym użytkownikom bezpiecznie i łatwo wysyłać dowolnie duże pliki innym użytkownikom. Użytkownicy nieposiadający konta mogą otrzymać kupon Gościa od uwierzytelnionego użytkownika, który umożliwia przesyłanie plików do serwisu. FileSender jest rozwijany zgodnie z wymogami szkolnictwa wyższego i społeczności badawczej.
</p>

<h4>Dla Gości...</h4>

<p>
    Jeśli otrzymałeś kupon gościa z tej witryny, to oznacza, że zostałeś zaproszony do przesłania plików do serwisu, w zależności od kuponu będziesz mógł to zrobić jeden lub więcej razy. Najprostszym sposobem na to jest skorzystanie z informacji zawartych w e-mailu z zaproszeniem. Przesyłając jako Gość, upewnij się, że wszelkie linki w wiadomości email z zaproszeniem, które otrzymałeś są skierowane do FileSender działającego w zaufanym ośrodku badawczym. Jeśli nie oczekujesz linku gościa ze znanego ośrodka, wiadomość email może być próbą wyłudzenia danych.
</p>
<p>
    Użytkownik, który Cię zaprosił , mógł pozwolić na przesyłanie plików i uzyskanie łącza umożliwiającego innym osobom pobieranie tych plików. Jeśli nie możesz uzyskać linku, musisz podać adresy email osób, które chcesz zaprosić do pobrania przesłanych plików.
</p>

<h4>Dla Uwierzytelnionych Użytkowników...</h4>

<p>
    Jeśli ta instalacja FileSender wykonana została w placówce badawczej, przycisk logowania w prawym górnym rogu strony powinien umożliwiać logowanie przy użyciu standardowego konta instytucjonalnego. Jeśli nie masz pewności, jakich danych logowania użyć, aby uzyskać dostęp do usługi FileSender, skontaktuj się z lokalnym działem wsparcia IT.
</p>

<p>
    Jako uwierzytelniony użytkownik powinieneś być w stanie przesłać pliki jeden lub więcej razy oraz mieć możliwość albo wysłać automatycznie poprzez FileSender'a pocztą email do odbiorców po zakończeniu przesyłania z linkiem do pobrania, albo móc pobrać link umożliwiający pobranie pliku. Powinieneś być również w stanie zaprosić innych badaczy do systemu, umożliwiając im przesłanie jednego lub więcej plików jako Gość.
</p>

<h3>Możliwe Ograniczenia Rozmiaru Pobieranych Plików</h3>
<p>
    Każda nowoczesna przeglądarka pobierze pliki o dowolnym rozmiarze ze strony. Do pobierania nie są wymagane żadne specjalne elementy.
</p>

<h3>Możliwe Ograniczenia Rozmiaru Wysyłanych Plików</h3>

<p>
    Jeśli Twoja przeglądarka obsługuje HTML5, wysyłanie powinno pozwalać na przesył plików o dowolnym rozmiarze do maksymalnej wielkości transferu {size:cfg:max_transfer_size}. Aktualne wersje Firefoksa i Chrome na Windows, Mac OS i Linux posiadają obsługę HTML5.
</p>

<h3>Funkcjonalność Twojej Przeglądarki</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 upload enabled" /> Możesz przesyłać pliki o dowolnym rozmiarze, maksymalnie do {size:cfg:max_transfer_size} na transfer oraz możesz wznawiać przesyłanie.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 upload disabled" /> Możesz przesłać pliki maksymalnie o rozmiarze {size:cfg:max_legacy_file_size} każdy aż do maksymalnej wielkości  {size:cfg:max_transfer_size} na transfer.</li>
</ul>

<h3>Wysyłanie <i>każdej wielkości pliku</i> z HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Będziesz mógł użyć tej metody, jeśli  <img src="images/html5_installed.png" alt="HTML5 upload enabled" /> znak zgodności jest wyświetlany</li>
    <li><i class="fa-li fa fa-caret-right"></i>Aby włączyć tę funkcjonalność, wystarczy użyć aktualnej przeglądarki obsługującej HTML5, najnowszą wersję „języka stron www”.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Aktualne wersje Firefox i Chrome na Windows, Mac OS X oraz Linux powinny działać.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Możesz <strong>wznawiać</strong> przerwane lub anulowane przesyłanie po prostu <strong>wysyłając dokładnie te same pliki</strong> jeszcze raz!
        Upewnij się że pliki posiadają <strong>te same nazwy i rozmiary</strong> co poprzednio.
        Gdy rozpocznie się wysyłanie, powinieneś zauważyć, że pasek postępu przeskakuje do miejsca, w którym przesyłanie zostało zatrzymane poprzednio i kontynuuj od tego momentu.
    </li>
</ul>

<h3>Wysyłanie plików o rozmiarze do {size:cfg:max_legacy_file_size} bez  HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>FileSender ostrzeże Cię, jeśli spróbujesz przesłać plik, który jest zbyt duży dla tej metody.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Wznawianie przesyłania nie jest obsługiwane przez tę metodę.</li>
</ul>

<h3>Skonfigurowane parametry usługi</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksymalna liczba odbiorców: </strong>{cfg:max_transfer_recipients} adresów email oddzielone przecinkiem lub średnikiem</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksymalna liczba plików na transfer: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksymalny rozmiar transferu: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Maksymalny rozmiar pliku dla przeglądarek niewspierających HTML5: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Ilość dni ważności transferu: </strong>{cfg:default_transfer_days_valid} (max. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Ilość dni ważności kuponu Gościa: </strong>{cfg:default_guest_days_valid} (max. {cfg:max_guest_days_valid})</li>
</ul

<h3>Szczegóły techniczne</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> używa <a href="http://filesender.org/" target="_blank">oprogramowania FileSender</a>.
        FileSender pokazuje, czy metoda przesyłania HTML5 jest obsługiwana dla określonej przeglądarki.         
        Zależy to głównie od dostępności zaawansowanych funkcji przeglądarki, w szczególności HTML5 FileAPI.
        Proszę użyć strony <a href="http://caniuse.com/fileapi" target="_blank">"When can I use..."</a> do monitorowania postępu wdrażania HTML5 FileAPI dla wszystkich głównych przeglądarek.
        W szczególności wsparcie dla <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> i<a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> musi być zaznaczone na zielono (= obsługiwany), aby przeglądarka mogła przesyłać pliki większe niż {size:cfg:max_legacy_file_size}.
        Należy pamiętać, że chociaż Opera 12 znajduje się na liście przeglądarek wspierających HTML5 FileAPI, obecnie nie obsługuje wszystkiego, co jest potrzebne do obsługi metody przesyłania HTML5 w FileSender.
    </li>
</ul>

<p>Aby uzyskać więcej informacji prosimy odwiedzić stronę <a href="http://filesender.org/" target="_blank">filesender.org</a></p>

