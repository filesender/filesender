<?php

$lang["_HELP_TEXT"] = '<div align="left" style="padding:5px">
    <p> Ta kontakt med din lokale IT-avdeling hvis p�logging feiler eller du ikke finner din institusjon i listen av hjemmeinstitusjoner. </p>
    <p> <strong>Systemkrav</strong><br>
      En nyere utgave av en popul�r nettleser</p>
    <p> <strong>Begrensninger</strong><br>
      <strong> Maks. antall mottakere per sending:</strong> '. $config["max_email_recipients"].' flere mottaker adresser skilles med komma eller semikolon (eks. ole@norge.no, per@uninettt.no)<br>
      <strong>Antall filer per sending:</strong> 1 - for � sende flere filer samtidig kan du pakke dem i feks. en zip-fil f�rst.<br>
      <strong>St�rste filst�rrelse per sending, uten HTML 5: </strong>'. formatBytes($config["max_flash_upload_size"]) .'<br>
      <strong>St�rste filst�rrelse per sending, med HTML 5: </strong>' .formatBytes($config["max_gears_upload_size"]).'<br>
      <strong>Maksimum antall dager f�r utl�p av sending: </strong>'. $config["default_daysvalid"].'<br>
    </p>
    <p>For mer informasjon bes�k <a href="http://www.filesender.org/">www.filesender.org</a></p>
  </div>';

$lang["_ABOUT_TEXT"] = ' <div align="left" style="padding:5px">'. htmlentities($config['site_name']) .' is an installation of FileSender (<a rel="nofollow" href="http://www.filesender.org/">www.filesender.org</a>), which is developed to the requirements of the higher education and research community.</div>';

$lang["_AUPTERMS"] = "Oppfør deg pent ellers så kommer trollan...";

?>
