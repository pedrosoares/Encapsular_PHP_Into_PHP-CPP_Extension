<?php
header ('Content-type: text/html; charset=UTF-8');
/**
 * Created by PhpStorm.
 * User: Pedro Augusto da Silva Soares
 * Date: 08/11/15
 * Time: 21:18
 */

$paths_tmp = explode("/", dirname(__FILE__));

$PATH_ATUAL = str_replace( $paths_tmp[count($paths_tmp)-1], "", str_replace(basename(__FILE__), "", str_replace("phar://", "", dirname(__FILE__) ) ) );
$WWW = "www/";
function show($message){
    echo $message."\r\n";
}

if(!file_exists($PATH_ATUAL."compiler.json")) {
    $config = [
        [ "app", "app/Http/Controllers" ],
        "IGNORE_FILES" => ["app/Http/Controllers/Controller.php"],
        "AUTOLOAD" => "/bootstrap/autoload.php"
    ];
    file_put_contents($PATH_ATUAL."compiler.json", json_encode($config));
    show( "\r\nCreated compiler.json file." );
    sleep(1);
    die();
}
show("Loading config....");
$PATHs = json_decode( file_get_contents($PATH_ATUAL."compiler.json"), true );
$IGNOREFILES = $PATHs["IGNORE_FILES"];
if(isset($PATHs["AUTOLOAD"])){
    $AUTOLOAD = $PATHs["AUTOLOAD"];
}
$PATHs = $PATHs[0];
show("Config loaded!");

sleep(1);

if(!isset($argv[1])) {
    show("\r\nPlease, informe your Extension/Project name!");
    die();
}

if(!isset($argv[2]) && !isset($AUTOLOAD)) {
    show("Your system have autoload file? If not, leave blank.");
    show("!! If your class include another without autoload, the system broken. !!");
    $cmd = readline();
    $AUTOLOAD = ($cmd == "" ? false : $cmd);
}

$NAME = $argv[1];

if(!isset($AUTOLOAD)) {
    $AUTOLOAD = $argv[2];
}else
if (!is_bool($AUTOLOAD) )
    if ($AUTOLOAD[0] == "/") {
        $AUTOLOAD[0] = "";
    }
if (strpos($AUTOLOAD, '..') !== false) {
    show("\r\nAuto file not can use ../ folder parameter!");
    die();
}

show("Seaching files...");

$FILES = [];

$finfo = finfo_open(FILEINFO_MIME_TYPE);

foreach($PATHs as $path){
    if ($handle = opendir($PATH_ATUAL.$path)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                if(finfo_file($finfo, $PATH_ATUAL.$path."/".$entry) === "text/x-php"){
                    if($entry != "Controller.php") {
                        $file_ponteiro = fopen($PATH_ATUAL . $path . "/" . $entry, "r");
                        $file_content = "std::string source = \"\";\r\n";
                        while(!feof($file_ponteiro)){
                            $file_content .= "source += \"".str_replace('<?php', '',str_replace("\n", " ", str_replace("\r\n", " ",addslashes(fgets($file_ponteiro)))))."\\r\\n\";\r\n";
                        }
                        fclose($file_ponteiro);

                        $levels = '';
                        for($i=0; $i < count(explode("/", $path)); $i++){
                            $levels .= '../';
                        }

                        $FILES[$entry] = [$path, $file_content, $levels];
                    }
                }
            }
        }
        closedir($handle);
    }
}

show( "Found (".count($FILES).") files " );
sleep(1);

$template  = "#include <phpcpp.h> \r\n";
$template .= "#include <iostream> \r\n";
$template .= "using namespace Php;  \r\n";

foreach($FILES as $key=>$value) {
    $template .= "\r\n \r\n";
    $template .= "Php::Value ".str_replace(".php", "", $key)." () {  \r\n";
    $template .= "  ".$value[1]."\r\n";
    if(!is_bool($AUTOLOAD) and isset($AUTOLOAD)) {
        $value_path_tmp = trim("include_once(\"".$value[2] ."".trim($AUTOLOAD) ."\");");
        var_dump($value_path_tmp);
        $template .= $value_path_tmp . " \r\n";
    }
    $template .= "  return Script(source).execute(); \r\n";
    $template .= "}  \r\n";
    $template .= "\r\n\r\n";
}

$template .= "extern \"C\" {  \r\n ";
$template .= "    PHPCPP_EXPORT void *get_module() {  \r\n ";
$template .= "        static Php::Extension extension(\"{$NAME}\", \"1.0\");  \r\n ";
foreach($FILES as $key=>$value) {
    $template .= "        extension.add(\"".str_replace(".php", "", $key)."\", ".str_replace(".php", "", $key).");  \r\n ";
}
$template .= "        return extension;  \r\n ";
$template .= "    } \r\n ";
$template .= "}  \r\n ";





show(  "Compile finished, creating files... " );
sleep(1);
if(!file_exists($PATH_ATUAL."build_folder")) {
    mkdir($PATH_ATUAL."build_folder");
}
if(!file_exists($PATH_ATUAL."build_folder/".$WWW)) {
    mkdir($PATH_ATUAL."build_folder/".$WWW);
}

foreach($FILES as $key=>$value) {
    $paths = explode("/", $value[0] );
    $tmp = "";
    foreach($paths as $pt) {
        $tmp .= $pt;
        if (!file_exists($PATH_ATUAL."build_folder/".$WWW . $tmp)) {
            mkdir($PATH_ATUAL."build_folder/".$WWW . $tmp);
        }
        $tmp .= "/";
    }
    $ponteiro = fopen($PATH_ATUAL."build_folder/".$WWW.$value[0]."/".$key, "w");
    fwrite($ponteiro, "<?php \r\n".str_replace(".php", "", $key)."();");
    fclose($ponteiro);
}


$makefile = "";
$makefile .= "NAME				    =	{$NAME}\n";
$makefile .= "INI_DIR				=	/etc/php5/conf.d\n";
$makefile .= "EXTENSION_DIR		    =	$(shell php-config --extension-dir)\n";
$makefile .= 'EXTENSION 			=	${NAME}.so'."\n";
$makefile .= 'INI 				    =	${NAME}.ini'."\n";
$makefile .= "COMPILER			    =	g++\n";
$makefile .= "LINKER				=	g++\n";
$makefile .= "PHP_CONFIG			=	php-config\n";
$makefile .= "COMPILER_FLAGS		=	-Wall -c -O2 -std=c++11 -fpic -o\n";
$makefile .= "LINKER_FLAGS		    =	-shared\n";
$makefile .= "LINKER_DEPENDENCIES	=	-lphpcpp \n";
$makefile .= "RM					=	rm -f\n";
$makefile .= "CP					=	cp -f\n";
$makefile .= "MKDIR				    =	mkdir -p\n";
$makefile .= 'SOURCES				=	$(wildcard *.cpp)'."\n";
$makefile .= 'OBJECTS				=	$(SOURCES:%.cpp=%.o)'."\n";
$makefile .= 'all:					${OBJECTS} ${EXTENSION}'."\n";
$makefile .= '${EXTENSION}:			${OBJECTS}'."\n";
$makefile .= '						${LINKER} ${LINKER_FLAGS} -o $@ ${OBJECTS} ${LINKER_DEPENDENCIES}'."\n";
$makefile .= '${OBJECTS}:'."\n";
$makefile .= '						${COMPILER} ${COMPILER_FLAGS} $@ ${@:%.o=%.cpp}'."\n";
$makefile .= "install:		\n";
$makefile .= '						${CP} ${EXTENSION} ${EXTENSION_DIR}'."\n";
$makefile .= '						${CP} ${INI} ${INI_DIR}'."\n";
$makefile .= "				\n";
$makefile .= "clean:\n";
$makefile .= '						${RM} ${EXTENSION} ${OBJECTS}'."\n";

$ponteiro = fopen($PATH_ATUAL."build_folder/main.cpp", "w");
fwrite($ponteiro, $template);
fclose($ponteiro);

$ponteiro = fopen($PATH_ATUAL."build_folder/Makefile", "w");
fwrite($ponteiro, $makefile);
fclose($ponteiro);

$ponteiro = fopen($PATH_ATUAL."build_folder/{$NAME}.ini", "w");
fwrite($ponteiro, "extension={$NAME}.so\r\n");
fclose($ponteiro);


show(  "Finished, now you can compile it. \r\n" );
sleep(1);
