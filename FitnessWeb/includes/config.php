<?php

/** Parámetros de conexión a la BD */
define('BD_HOST', 'localhost');
define('BD_NAME', 'lifety');
define('BD_USER', 'lifetyuser');
define('BD_PASS', 'lifetypass');

/** Parámetros de configuración utilizados para generar las URLs y las rutas a ficheros en la aplicación */
define('RAIZ_APP', __DIR__);
define('RUTA_APP', '/AW/GitHub/P4');
define('RUTA_IMGS', RUTA_APP.'/src/img');
define('RUTA_CSS', RUTA_APP.'/src/css');
define('RUTA_JS', RUTA_APP.'/src/js');
define('RUTA_ALMACEN_EJERCICIOS', implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'src', 'img', 'ejercicios']));
define('RUTA_ALMACEN_ANUNCIOS', implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'src', 'img', 'anuncios']));
define('RUTA_ALMACEN_PRODUCTOS', implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'src', 'img', 'productos']));
/** Configuración del soporte de UTF-8, localización (idioma y país) y zona horaria */
ini_set('default_charset', 'UTF-8');
setLocale(LC_ALL, 'es_ES.UTF.8');
date_default_timezone_set('Europe/Madrid');

/**
 * Función para autocargar clases PHP.
 * @see http://www.php-fig.org/psr/psr-4/
 */
spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = '';
    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/clases/';
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    // get the relative class name
    $relative_class = substr($class, $len);
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Gestor global de excepciones.
 * @param Throwable $exception
 */
function gestorExcepciones(Throwable $exception) {
    error_log(jTraceEx($exception)); 
    http_response_code(500);
    $msg = $exception->getMessage();
    $msg1 = $exception->getFile();
    $msg2 = $exception->getLine();
    $tituloPagina = 'Error';
    $contenidoPrincipal = <<<EOS
        <h1>Oops</h1>
        <p> $msg </p>
        <p> $msg1 </p>
        <p> $msg2 </p>
    EOS;
    require __DIR__.'/vistas/plantillas/plantilla.php';
}

set_exception_handler('gestorExcepciones');

// http://php.net/manual/es/exception.gettraceasstring.php#114980
/**
 * jTraceEx() - provide a Java style exception trace
 * @param Throwable $exception
 * @param string[] $seen Array passed to recursive calls to accumulate trace lines already seen leave as NULL when calling this function
 * @return string  string stack trace, one entry per trace line
 */
function jTraceEx($e, $seen=null) {
    $starter = $seen ? 'Caused by: ' : '';
    $result = array();
    if (!$seen) $seen = array();
    $trace  = $e->getTrace();
    $prev   = $e->getPrevious();
    $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
    $file = $e->getFile();
    $line = $e->getLine();
    while (true) {
        $current = "$file:$line";
        if (is_array($seen) && in_array($current, $seen)) {
            $result[] = sprintf(' ... %d more', count($trace)+1);
            break;
        }
        $result[] = sprintf(' at %s%s%s(%s%s%s)',
            count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
            count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
            count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
            $line === null ? $file : basename($file),
            $line === null ? '' : ':',
            $line === null ? '' : $line);
        if (is_array($seen))
            $seen[] = "$file:$line";
        if (!count($trace))
            break;
        $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
        $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
        array_shift($trace);
    }
    $result = join(PHP_EOL , $result);
    if ($prev)
        $result  .= PHP_EOL . jTraceEx($prev, $seen);
        
    return $result;
}

// Inicializa la aplicación
$app = appweb\Aplicacion::getInstance();
$app->init(array('localhost'=>BD_HOST, 'lifety'=>BD_NAME, 'lifetyuser'=>BD_USER, 'lifetypass'=>BD_PASS), RUTA_APP, RAIZ_APP);

/**
 * @see http://php.net/manual/en/function.register-shutdown-function.php
 * @see http://php.net/manual/en/language.types.callable.php
 */
register_shutdown_function([$app, 'shutdown']);