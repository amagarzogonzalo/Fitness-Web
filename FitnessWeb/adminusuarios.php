<?php

require_once __DIR__.'/includes/config.php';

$form1 = new \appweb\admin\FormularioAdminCrea();
$html1 = $form1->gestiona();
//$form2 = new \appweb\admin\FormularioAdminBorra();
//$html2 = $form2->gestiona();

$tituloPagina = 'Consola';
$contenidoPrincipal = <<<EOS
<h1>Consola de Administracion</h1>
<div id="consola">
$html1
</div>
EOS;
require __DIR__.'/includes/vistas/plantillas/plantilla.php';