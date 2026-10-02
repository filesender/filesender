<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: ඔබේ {if:target_type=="recipient"}ලබන්නා{endif}{if:target_type=="guest"}ාආගන්තුක පරිශීලක{endif} {target.email} වෙතින් ප්‍රතිපෝෂණ

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

ඔබගේ {if:target_type=="recipient"}ලබන්නා{endif}{if:target_type=="guest"}ආගන්තුක පරිශීලකයා{endif} {target.email} වෙතින් අපට විද්‍යුත් තැපැල් ප්‍රතිපෝෂණයක් ලැබුණි, කරුණාකර එය අමුණා ඇති බව සොයා ගන්න.

සුභ පතමින්,
{cfg:site_name}

{alternative:html}

<p>
    හිතවත් මහත්මයා හෝ මැතිණියනි,
</p>

<p>
    ඔබගේ {if:target_type=="recipient"}ලබන්නා{endif}{if:target_type=="guest"}ආගන්තුකයා{endif} {target.email} වෙතින් අපට විද්‍යුත් තැපැල් ප්‍රතිපෝෂණයක් ලැබුණි, කරුණාකර එය අමුණා ඇති බව සොයා ගන්න.
</p>

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>