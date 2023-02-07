<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: ගබඩා භාවිතය අනතුරු ඇඟවීම

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

{cfg:site_name} හි ගබඩා භාවිතය අනතුරු අඟවයි:

{each:warnings as warning}  
- {warning.filesystem} ({size:warning.total_space}) ඉතිරිව ඇත්තේ {size:warning.free_space} පමණි ({warning.free_space_pct}%)
{endeach}

ඔබට {cfg:site_url} හි අමතර විස්තර සොයා ගත හැක

සුභ පතමින්,
{cfg:site_name}

{alternative:html}

<p>
    හිතවත් මහත්මයා හෝ මැතිණියනි,
</p>

<p>
    {cfg:site_name} හි ගබඩා භාවිතය අනතුරු අඟවයි:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) ඉතිරිව ඇත්තේ {size:warning.free_space} පමණි ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    ඔබට <a href="{cfg:site_url}">{cfg:site_url}</a> හි අමතර විස්තර සොයා ගත හැක
</p>

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>