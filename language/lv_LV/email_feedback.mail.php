<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Atgriezeniskā saite no {if:target_type=="recipient"}adresāta{endif}{if:target_type=="guest"}viesa{endif}#{target_id} {target.email}

{alternative:plain}

Godātais kungs vai cienījamā kundze,

Mēs saņēmām atgriezeniskās saites e-pastu no {if:target_type=="recipient"}adresāta{endif}{if:target_type=="guest"}viesa{endif}#{target_id} {target.email}, lūdzu, atrodiet to pievienotu.

Ar cieņu
{cfg:site_name}

{alternative:html}

<p>
    Godātais kungs vai cienījamā kundze,
</p>

<p>
    Mēs saņēmām atgriezeniskās saites e-pastu no {if:target_type=="recipient"}adresāta{endif}{if:target_type=="guest"}viesa{endif}#{target_id} {target.email}, lūdzu, atrodiet to pievienotu.
</p>

<p>
   Ar cieņu<br />
    {cfg:site_name}
</p>