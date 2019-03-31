<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h1>Benvenuto in {cfg:site_name}</h1>
<p>
    Affinché questo servizio funzioni, deve conservare alcune informazioni sui file, chi può accedervi e cosa è accaduto. I file verranno automaticamente rimossi dal sistema quando scadono e verranno rimosse altre informazioni conservate dal sistema e dal database dopo che è trascorso un po' di tempo. Questa pagina ti permette di vedere pre quanto tempo le informazioni mantenuto da questa installazione.
</p>
<p>
    Si noti che quando un trasferimento viene eliminato, tutti i file correlati sono anche cancellati insieme alle copie di tutte le email inviate che riguardano il trasferimento.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Questo sito è configurato per distruggere i file caricati quando vengono eliminati. ";
    echo "La distruzione di un file comporta la scrittura di dati nella stessa posizione sul disco";
    echo " molte volte per rimuovere veramente i dati utente dal sistema. ";
    echo "Questo fornisce ulteriore privacy per gli utenti del servizio. </p>";
}
?>