<?php
$phar = new Phar("compiler.phar",  FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, "compiler.phar");
$phar["index.php"] = file_get_contents("compiler.php");
$phar->setStub($phar->createDefaultStub("index.php"));