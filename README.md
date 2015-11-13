#Compilador de Arquivos/Projetos PHP

Este é um script em PHP que transforma seus arquivos ou projetos em extensão PHP, após utiliza-lo será gerado dentro da pasta www os arquivos PHP que as funções da nova extensão, além dos arquivos necessários para compilar a extensão.

## Como Utilizar

- Copie o arquivo compiler.phar para a pasta root do projeto.
- Pelo Terminal execute o comando "php compiler.phar Nome_da_extensão", substitua Nome_da_extensão por algum nome para sua extensão.
- Será gerado um arquivo chamado compiler.json, neste arquivo adicione no campo "0" as pastas onde o script irá procurar por arquivos PHP.
- Execute novamente o comando "php compiler.phar Nome_da_extensão".
- Será perguntado se o seu sistema possui um arquivo responsavel pelo auto include, este arquivo normalmente possui o nome de autoload, no caso da framework laravel. Deixe em branco caso não possua.
- Entre na basta build_files e edite o arquivo "Makefile", adicione o local que fica os arquivos .ini do PHP, caso este arquivo não exista, você deverá adicionar manualmente no arquivo php.ini.
- Execute o seguinte comando "make clean && make && sudo make install"
- Faça backup do seu projeto, remova do apache e copie todos os aquivos da pasta www para a pasta do apache.
- Abra o site no navegador.
