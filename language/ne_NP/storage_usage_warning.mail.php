<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
{alternative:plain}

प्रिय महोदय वा महोदया,

{cfg:site_name} को भण्डारण प्रयोग चेतावनी हो:

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) मा {size:warning.free_space} मात्र बाँकी छ ({warning.free_space_pct}%)
{endeach}

तपाईंले {cfg:site_url} मा थप विवरणहरू फेला पार्न सक्नुहुन्छ

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    {cfg:site_name} को भण्डारण प्रयोग चेतावनी हो:
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) मा {size:warning.free_space} मात्र बाँकी छ ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    तपाईंले <a href="{cfg:site_url}">{cfg:site_url}</a> मा अतिरिक्त विवरणहरू फेला पार्न सक्नुहुन्छ
</p>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>