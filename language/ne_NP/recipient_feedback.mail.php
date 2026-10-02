<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: तपाईंको {if:target_type=="recipient"}प्रापक{endif}{if:target_type=="guest"}गेस्ट{endif} {target.email} बाट प्रतिक्रिया

{alternative:plain}

प्रिय महोदय वा महोदया,

हामीले तपाईंको {if:target_type=="recipient"}प्रापक{endif}{if:target_type=="guest"}गेस्ट{endif} {target.email} बाट एउटा इमेल प्रतिक्रिया प्राप्त गर्‍यौं, कृपया यसलाई संलग्न गरिएको छ।

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    हामीले तपाईंको {if:target_type=="recipient"}प्रापक{endif}{if:target_type=="guest"}गेस्ट{endif} {target.email} बाट एउटा इमेल प्रतिक्रिया प्राप्त गर्‍यौं, कृपया यसलाई संलग्न गरिएको छ।
</p>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>