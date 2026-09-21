<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
Subiect: Feedback de la {if:target_type=="recipient"}destinatar{endif}{if:target_type=="guest"}invitat{endif}#{target_id} {target.email}

{alternative:plain}

Stimate Domnule / Stimată Doamnă,

Am primit un feedback prin e-mail de la {if:target_type=="recipient"}destinatar{endif}{if:target_type=="guest"}invitat{endif}#{target_id} {target.email}, vă rugăm să-l găsiți atașat.

Cu respect,
{cfg:site_name} 

{alternative:html}

<p> 
Stimate Domnule / Stimată Doamnă, 
</p>

<p> 
Am primit un feedback prin e-mail de la {if:target_type=="recipient"}destinatar{endif}{if:target_type=="guest"}invitat{endif}#{target_id} {target.email}, vă rugăm să-l găsiți atașat. 
</p>

<p> 
Cu respect,<br /> 
{cfg:site_name} 
</p>