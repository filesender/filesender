<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
<h3>Acesso</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i> O acesso ao serviço é feito por meio de um dos Provedores de Identidade da Federação CAFe. Se sua instituição não consta na lista de clientes da CAFe ou se tiver problemas na autenticação, contacte o técnico responsável em sua instituição.</li>
</ul>

<h3>Uploads de <i>qualquer tamanho</i> com HTML5</h3> 
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i>Este modo estará disponível se o seguinte ícone for exibido: <img src="images/html5_installed.png" alt="HTML5 upload enabled" /></li>
    <li><i class="fa-li fa fa-caret-right"></i>ara visualizar o ícone <img src="images/html5_installed.png" alt="HTML5 upload enabled" />, basta utilizar um browser atualizado e compatível com HTML5.</li>
    <li><i class="fa-li fa fa-caret-right"></i>Versões atualizadas do Firefox e do Chrome para Windows, Mac OS X e Linux suportam esta tecnologia.</li>
    <li><i class="fa-li fa fa-caret-right"></i>
        Você pode <strong>retomar</strong> É possível <b><i>retomar</i></b> um upload cancelado ou interrompido. Para retomar o upload, basta enviar o mesmo arquivo novamente. Certifique-se de que o nome do arquivo não foi alterado e o {cfg:site_name} o reconhecerá. Quando o upload reiniciar, a barra de progresso avançará até o ponto em que o upload havia parado anteriormente e continuará a partir dali.<br><br> Se o arquivo <b><i>tiver sido modificado</i></b> entre a primeira e a segunda tentativas de envio, renomeie-o antes.  Esta ação garante que um novo upload será iniciado e que as mudanças no arquivo serão enviadas corretamente.
    </li>
</ul>

<h3>Downloads de qualquer tamanho</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i> Os downloads devem funcionar na maior parte dos browsers. Os requisitos de Adobe Flash e HTML5 são válidos apenas para uploads.</li>
</ul>

<h3>Upload de arquivos menores que 2 gigabytes (2GB) com Adobe Flash</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i> Se o browser suporta a execução de vídeos do Youtube, este modo de upload deve funcionar normalmente</li>
	<li><i class="fa-li fa fa-caret-right"></i> É necessário o uso de um browser contendo o plugin <a target="_blank" href="http://www.adobe.com/software/flash/about/" draggable="false">Adobe Flash</a> na versão 10 ou superior.</li>
	<li><i class="fa-li fa fa-caret-right"></i> Com o uso do Adobe Flash, é possível carregar arquivos de até 2 Gigabytes (2GB). O {cfg:site_name} avisará o usuário caso o arquivo carregado exceda este tamanho</li>
	<li><i class="fa-li fa fa-caret-right"></i> Neste método não é possível recuperar uploads interrompidos</li>
</ul>

<h3>Limites definidos para o serviço</h3>
<ul class="fa-ul">
    <li><i class="fa-li fa fa-caret-right"></i> <strong>Número máximo de destinatários por e-mail:</strong> Até 100 endereços de e-mail separados por vírgula ou ponto-e-vírgula</li>
	<li><i class="fa-li fa fa-caret-right"></i> <strong>Número máximo de arquivos por upload:</strong> um - para carregar múltiplos arquivos, deve-se compactá-los em um único antes do envio</li>
	<li><i class="fa-li fa fa-caret-right"></i> <strong>Tamanho máximo de arquivo por upload, utilizando apenas Adobe Flash:</strong> 2 GB</li>
	<li><i class="fa-li fa fa-caret-right"></i> <strong>Tamanho máximo de arquivo por upload, utilizando HTML5:</strong> 150 GB</li>
	<li><i class="fa-li fa fa-caret-right"></i> <strong>Validade máxima em dias do arquivo/voucher:</strong> 20</li>
</ul>

<h3>Detalhes técnicos</h3>
<ul class="fa-ul">
	<li><i class="fa-li fa fa-caret-right"></i> O {cfg:site_name} utiliza o software <a href="http://www.filesender.org/" target="_blank" draggable="false">FileSender</a>. O FileSender indica se o upload por HTML5 é ou não suportado pelo browser utilizado. Este modo de upload depende principalmente da disponibilidade da FileAPI HTML. Utilize o site <a href="http://caniuse.com/fileapi" target="_blank" draggable="false">"When can I use..."</a> para verificar ao progresso da implementação da FileAPI HTML5 nos browsers mais comuns. Especificamente, o suporte a <a href="http://caniuse.com/filereader" target="_blank" draggable="false">FileReader API</a> e <a href="http://caniuse.com/bloburls" target="_blank" draggable="false">Blob URLs</a> deve estar verde-claro para que um browser suporte upload de arquivos maiores que 2GB.  Note que, apesar de o Opera 12 estar listado com compatível com a FileAPI HTML5, o browser não suporta todos os requisitos necessários para uso do upload por HTML5 do FileSender.</li>
</ul>

<p>Para mais informações, visite o site <a href="http://www.filesender.org/" target="_blank" draggable="false">www.filesender.org</a></p>