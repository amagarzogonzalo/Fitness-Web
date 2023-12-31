<?php
namespace appweb\plan;

use appweb\Formulario;
use appweb\Aplicacion;
use appweb\usuarios\Usuario;
use appweb\usuarios\Profesional;


class FormularioPlanEntrenadorDieta extends Formulario {
    public function __construct() {
        parent::__construct('formEntrenadorDietas');
    }
    
    private function Usuarios(){
        $rts = "";
        $app = Aplicacion::getInstance();
        $usuarios = Profesional::getUsuariosDieta($app->nombreUsuario());
        foreach ($usuarios as &$valor) {
            $rts = $rts ."<option value='$valor'>$valor</option>";
        }
       return $rts;
    }

    protected function generaCamposFormulario(&$datos) {
        $alias = $datos['alias'] ?? '';

        $app = Aplicacion::getInstance();

        // Se generan los mensajes de error si existen.
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['alias'], $this->errores, 'span', array('class' => 'error'));
    
        $SelectUsuarios = self::Usuarios();
        $boton ="";
        $Select = "<h1>Editar Dieta de usuarios con Seguimiento</h1>"; 
        if($SelectUsuarios == "") $Select .= "<p>No hay usuarios disponibles.</p>";
        else {
            $Select .= "<p>Elija al usuario al que quiere editar su dieta<p><div><select name = 'alias' id = 'alias' type = 'text'>";
            $Select .= $SelectUsuarios;
            $Select .= "</select>";
            $boton = "<button type='submit' name='enviar'>Editar dieta </button>";
        }
        // Se genera el HTML asociado a los campos del formulario y los mensajes de error.
        $html = <<<EOF
        $htmlErroresGlobales
                        $Select
                    {$erroresCampos['alias']}
                        $boton
           
        EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos) {
        $this->errores = [];
        htmlspecialchars(trim(strip_tags($_POST["alias"])));
        $alias      = trim($datos["alias"] ?? '');

        if (count($this->errores) === 0) {
            $app = Aplicacion::getInstance();
            $usuario = Usuario::buscaPorAlias($alias);
            $idUsuario = $usuario->getId();
            $this->urlRedireccion = $app->buildUrl('/planeditardieta.php', ['idUsuario' => $idUsuario]);
            
        }
    }
}
