<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Отзыв от {if:target_type=="recipient"}получателя{endif}{if:target_type=="guest"}гостя{endif} {target.email}

{alternative:plain}

Товарищ!

Мы получили отзыв от твоего {if:target_type=="recipient"}получателя{endif}{if:target_type=="guest"}гостя{endif} {target.email}.
Его можно найти в приложении.

С наилучшими пожеланиями,
{cfg:site_name}

{alternative:html}

<p>
  Товарищ
</p>

<p>
  Мы получили отзыв от твоего {if:target_type=="recipient"}получателя{endif}{if:target_type=="guest"}гостя{endif} {target.email}.
  Его можно найти в приложении.
</p>

<p>
    С наилучшими пожеланиями,<br />
    {cfg:site_name}
</p>
