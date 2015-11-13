<?php
    
//echo "Testing helloWorld in skeleton.so\n";
//echo helloWorld('Taylor');
//echo helloWorld(1234+5678);
    
   /// $a = (array) testeNovo();
    
    //echo $a[0]."\n";
    
    //var_dump( $a );

    //$c = EasyMysql();
    //$c->execute();
    
    //echo  template("header");
    
    

 ///*   $content = '
    //<?php class Novo{
        //public function hell(){
        //    return "Hell Low World";
      //  }
    //} ? //>
    //';


//    include "data://text/plain;base64,".base64_encode($content);

  //  $c = new Novo();
    //echo $c->hell();


    //echo encriptar("Pedro");
    //echo descriptar("Ujibo");


//$a = new Counter();
//$a->increment();
//echo $a->value(//);

//    HomeControllerInclude ();
    //echo novo::coco();


$phar = new Phar("compiler.phar",  FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, "compiler.phar");
$phar["index.php"] = file_get_contents("compiler.php");
$phar->setStub($phar->createDefaultStub("index.php"));

//copy($srcRoot . "/config.ini", $buildRoot . "/config.ini");