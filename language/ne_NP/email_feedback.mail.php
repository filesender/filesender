<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: बाट प्रतिक्रिया {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}

{alternative:plain}

प्रिय महोदय वा महोदया,

हामीले {if:target_type=="recipient"}प्रापक{endif}{if:target_type=="guest"}अतिथि{endif}#{target_id} {target.email} बाट एउटा इमेल प्रतिक्रिया प्राप्त गर्‍यौं, कृपया यसलाई संलग्न फेला पार्नुहोस्।

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
     प्रिय महोदय वा महोदया,
</p>

<p>
    हामीले {if:target_type=="recipient"}प्रापक{endif}{if:target_type=="guest"}अतिथि{endif}#{target_id} {target.email} बाट एउटा इमेल प्रतिक्रिया प्राप्त गर्‍यौं, कृपया यसलाई संलग्न फेला पार्नुहोस्।
</p>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>