<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Вхід у систему</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Ви входите через одного з перелічених постачальників ідентифікації, використовуючи свій стандартний інституційний обліковий запис. Якщо ви не бачите своєї установи в списку або вхід не вдається, будь ласка, зверніться до місцевої IT-підтримки.</li>
</ul>

<h3>Можливості вашого браузера</h3>
<ul class="fa-ul">
    <li data-feature="html5"><img src="images/html5_installed.png" alt="HTML5 завантаження увімкнено" /> Ви можете завантажувати файли будь-якого розміру до {size:cfg:max_transfer_size} на одне пересилання.</li>
    <li data-feature="nohtml5"><img src="images/html5_none.png" alt="HTML5 завантаження вимкнено" /> Ви можете завантажувати файли розміром до {size:cfg:max_legacy_file_size} кожен і до {size:cfg:max_transfer_size} на одне пересилання.</li>
</ul>

<h3>Завантаження <i style="font-style:italic">будь-якого розміру</i> за допомогою HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Ви зможете використовувати цей метод, якщо вище відображається знак <img src="images/html5_installed.png" alt="HTML5 завантаження увімкнено" /></li>
    <li><i class="fa-li fa fa-caret-right"></i>Щоб увімкнути цю функцію, просто використовуйте актуальний браузер із підтримкою HTML5 — останньої версії «мови вебу».</li>
    <li><i class="fa-li fa fa-caret-right"></i>Відомо, що працюють актуальні версії Firefox та Chrome на Windows, Mac OS X та Linux.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Ви можете <strong>відновити</strong> перерване або скасоване завантаження. Щоб відновити завантаження, просто <strong>надішліть ті самі файли</strong> ще раз!
        Переконайтеся, що файли мають <strong>ті самі назви та розміри</strong>, що й раніше.
        Коли завантаження розпочнеться, ви помітите, що смуга прогресу стрибне до місця, де завантаження було зупинене, і продовжить роботу з цієї точки.
    </li>
</ul>

<h3>Завантаження до {size:cfg:max_legacy_file_size} на файл без HTML5</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>{cfg:site_name} попередить вас, якщо ви спробуєте завантажити файл, який є занадто великим для цього методу.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Відновлення завантажень не підтримується цим методом.</li>
</ul>

<h3>Завантаження будь-якого розміру</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Підійде будь-який сучасний браузер, для завантаження файлів нічого особливого не потрібно.</li>
</ul>

<h3>Налаштовані обмеження сервісу</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i><strong>Максимальна кількість отримувачів: </strong>{cfg:max_transfer_recipients} email-адрес, розділених комою або крапкою з комою.</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Максимальна кількість файлів на одне пересилання: </strong>{cfg:max_transfer_files}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Максимальний обсяг на одне пересилання: </strong>{size:cfg:max_transfer_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Максимальний розмір одного файлу для браузерів без HTML5: </strong>{size:cfg:max_legacy_file_size}</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Термін дії пересилання (дні): </strong>{cfg:default_transfer_days_valid} (макс. {cfg:max_transfer_days_valid})</li>
    <li><i class="fa-li fa fa-caret-right"></i><strong>Термін дії ваучера гостя (дні): </strong>{cfg:default_guest_days_valid} (макс. {cfg:max_guest_days_valid})</li>
</ul>

<h3>Технічні деталі</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>
        <strong>{cfg:site_name}</strong> використовує <a href="http://www.filesender.org/" target="_blank">програмне забезпечення FileSender</a>.
        FileSender вказує, чи підтримується метод завантаження HTML5 для конкретного браузера.
        Це залежить переважно від доступності розширених функцій браузера, зокрема HTML5 FileAPI.
        Будь ласка, використовуйте сайт <a href="http://caniuse.com/fileapi" target="_blank">«When can I use...»</a>, щоб стежити за прогресом впровадження HTML5 FileAPI для всіх основних браузерів.
        Зокрема, підтримка <a href="http://caniuse.com/filereader" target="_blank">FileReader API</a> та <a href="http://caniuse.com/bloburls" target="_blank">Blob URLs</a> має бути позначена світло-зеленим кольором (=підтримується), щоб браузер міг завантажувати файли розміром понад {size:cfg:max_legacy_file_size}.
        Зверніть увагу, що хоча Opera 12 заявлена як така, що підтримує HTML5 FileAPI, наразі вона не підтримує всі необхідні функції для роботи методу завантаження HTML5 у FileSender.
    </li>
</ul>

<p>Для отримання додаткової інформації відвідайте <a href="http://www.filesender.org/" target="_blank">www.filesender.org</a></p>
