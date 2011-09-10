<?php

$lang["_HELP_TEXT"] = '<div align="left" style="padding:5px">
    <p> Ta kontakt med din lokale IT-avdeling hvis pålogging feiler eller du ikke finner din institusjon i listen av hjemmeinstitusjoner. </p>
    <p> <strong>Systemkrav</strong><br>
      En nyere utgave av en populær nettleser</p>
    <p> <strong>Begrensninger</strong><br>
      <strong> Maks. antall mottakere per sending:</strong> '. $config["max_email_recipients"].' flere mottaker adresser skilles med komma eller semikolon (eks. ole@norge.no, per@uninettt.no)<br>
      <strong>Antall filer per sending:</strong> 1 - for å sende flere filer samtidig kan du pakke dem i feks. en zip-fil først.<br>
      <strong>Største filstørrelse per sending, uten HTML 5: </strong>'. formatBytes($config["max_flash_upload_size"]) .'<br>
      <strong>Største filstørrelse per sending, med HTML 5: </strong>' .formatBytes($config["max_gears_upload_size"]).'<br>
      <strong>Maksimum antall dager før utløp av sending: </strong>'. $config["default_daysvalid"].'<br>
    </p>
    <p>For mer informasjon besøk <a href="http://www.filesender.org/">www.filesender.org</a></p>
  </div>';

$lang["_ABOUT_TEXT"] = ' <div align="left" style="padding:5px">'. htmlentities($config['site_name']) .' is an installation of FileSender (<a rel="nofollow" href="http://www.filesender.org/">www.filesender.org</a>), which is developed to the requirements of the higher education and research community.</div>';

$lang["_AUPTERMS"] = "Oppfør deg pent ellers så kommer trollan...";

?>
