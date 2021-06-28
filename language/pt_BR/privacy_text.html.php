<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<p>
    Para que este serviço funcione, ele deve reter algumas
    informações sobre arquivos, quem pode acessá-los e o que
    aconteceu no processo de transferência. Os arquivos serão
    automaticamente removidos do sistema quando eles expiram
    e outras informações retidas serão removidas do banco de
    dados após algum tempo. Esta página permite que você
    veja por quanto tempo várias informações são retidas por
    esta instalação.
</p>
<p>
    Observe que quando uma transferência é excluída, todos os arquivos relacionados são
    também excluídos juntamente com as cópias de todos os e-mails que foram enviados
    relacionadas com a transferência.
</p>
<?php
if( ShredFile::shouldUseShredFile()) {
    echo "<p>Este site está configurado para destruir os arquivos enviados quando eles são excluídos. ";
    echo "A destruição de um arquivo envolve a gravação de dados no mesmo local no disco";
    echo " executada muitas vezes para realmente remover os dados do usuário do sistema. ";
    echo "Isso fornece privacidade adicional para os usuários deste serviço.</p>";
}
?>
