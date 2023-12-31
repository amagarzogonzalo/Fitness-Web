<?php
namespace appweb\foro;

use appweb\Formulario;
use appweb\Aplicacion;
use appweb\foro\Mensaje;

class FormularioCreaMensaje extends Formulario {
    public function __construct() {
        parent::__construct('formForo');
    }

    protected function generaCamposFormulario(&$datos) {
        $titulo = $datos['titulo'] ?? '';
        $mensaje = $datos['mensaje'] ?? '';
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['titulo', 'mensaje'], $this->errores, 'span', array('class' => 'error'));
        // Se genera el HTML asociado a los campos del formulario y los mensajes de error.
        $html = <<<EOF
        $htmlErroresGlobales
        {$erroresCampos['titulo']}
        <input id="titulo" type="text" name="titulo" value="$titulo" placeholder="titulo" />
        {$erroresCampos['mensaje']}
        <textarea id="mensaje" type="text" name="mensaje" value="$mensaje" placeholder="Empieza a escribir aqui el mensaje..."></textarea>
        <button type="submit" name="enviar">Responde a este mensaje</button>
        EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos) { 
        $this->errores = [];

        $titulo = $datos['titulo'] ?? '';
        $mensaje = $datos['mensaje'] ?? '';

        $titulo = filter_var($titulo, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$titulo || empty($titulo))
            $this->errores['titulo'] = '¿Cuál es el titulo del mensaje?';
        if (mb_strlen($titulo) > Mensaje::MAX_SIZE_TITLE)
            $this->errores['titulo'] = 'Longitud maxima 50';

        $mensaje = filter_var($mensaje, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$mensaje || empty($mensaje) || mb_strlen($mensaje) > Mensaje::MAX_SIZE)
            $this->errores['mensaje'] = 'Mensaje invalido, tamaño excedido.';

        $idpadre = $_GET['id'] ?? null;
        $idforo = $_GET['idforo'] ?? null;

        if (count($this->errores) === 0) {
            try {
                $app = Aplicacion::getInstance();
                $prioridad = 0;
                if ($idpadre) {
                    $padre = Mensaje::buscaxID($idpadre);
                    $idforo = $padre->getIDForo();
                    $prioridad = $padre->getPrioridad() + 1;
                }
                $msg = Mensaje::creaMensaje($app->idUsuario(), $idforo, $mensaje, $prioridad, $titulo, $idpadre);
                /* POP UP */
                $mensajes = ['Se ha creado el mensaje'];
                $app->putAtributoPeticion('mensajes', $mensajes);
            } catch (\Exception $e) {
                $this->errores[] = 'Este tema ya existe.';
           }
           if ($idpadre) {
            $return = $msg->getMsgOrigen();
            $this->urlRedireccion = $app->buildUrl('/foromensajes.php', ['id' => $return]);
        }/* else {
            $this->urlRedireccion = $app->buildUrl('/foroindividual.php', ['idforo' => $idforo]);
        }*/
        }
    }
}