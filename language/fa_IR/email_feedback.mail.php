<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
بازخورد از {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}

{alternative:plain}

خانم / آقا
یک باز خورد از {if:target_type=="recipient"}گیرنده{endif}{if:target_type=="guest"}مهمان{endif}#{target_id} {target.email}، لطفا آن را .

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    We received an email feedback from {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}, please find it enclosed.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>