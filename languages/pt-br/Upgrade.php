<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Igor Feghali <ifeghali@php.net>"
 * "Language-Team: PT-BR"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_PT-BR_UPGRADE_INTRODUCTION', "Introdução");
define('_PT-BR_UPGRADE_AUTHENTICATION', "Autenticação");
define('_PT-BR_UPGRADE_REQUIREMENTS', "Requisitos");
define('_PT-BR_UPGRADE_DATABASE', "Base de Dados");
define('_PT-BR_UPGRADE_REPORT', "Relatório");
define('_PT-BR_UPGRADE_VER_TO_VER', "{0} para {1}");
define('_PT-BR_UPGRADE_SETTINGS', "Configurações");
define('_PT-BR_UPGRADE_WRITECONFIG', "Salvar Configuração");
define('_PT-BR_UPGRADE_FINISHED', "Finalizar");
define('_PT-BR_UPGRADE_INTRO_WELCOME', "Bem-vindo à atualização do Jaws.");
define('_PT-BR_UPGRADE_INTRO_UPGRADER', "Aqui você poderá atualizar a sua versão antiga do Jaws para a mais recente. Por favor certifique-se de que você tem disponível");
define('_PT-BR_UPGRADE_INTRO_DATABASE', "Os detalhes do banco de dados - endereço, usuário, senha, nome da base de dados.");
define('_PT-BR_UPGRADE_INTRO_FTP', "Uma forma de enviar arquivos, provavelmente FTP.");
define('_PT-BR_UPGRADE_INTRO_LOG', "Logar o processo de atualização no arquivo {0}");
define('_PT-BR_UPGRADE_INTRO_LOG_ERROR', "Nota: se você desejar logar o processo de atualização, primeiro conceda ao servidor web permissão de escrita para o diretório {0}, e então atualize esta página");
define('_PT-BR_UPGRADE_AUTH_PATH_INFO', "Para garantir que você é realmente o administrador deste site, por favor crie um arquivo chamado {0} no diretório de instalação do Jaws ({1}).");
define('_PT-BR_UPGRADE_AUTH_UPLOAD', "Você pode enviar o arquivo para o servidor da mesma forma que enviou a sua cópia do Jaws.");
define('_PT-BR_UPGRADE_AUTH_KEY_INFO', "O arquivo deve conter o código mostrado abaixo, e nada mais.");
define('_PT-BR_UPGRADE_AUTH_ENABLE_SECURITY', "Ativar instalação segura (Powered by RSA)");
define('_PT-BR_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Houve um erro ao gerar a chave RSA. Por favor tente novamente.");
define('_PT-BR_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Houve um erro ao gerar a chave RSA. Nenhuma extensão matemática está disponível.");
define('_PT-BR_UPGRADE_AUTH_ERROR_KEY_FILE', "O arquivo de chave ({0}) não foi encontrado, por favor certifique-se de que você o criou e que o servidor web tem permissão de leitura sobre ele.");
define('_PT-BR_UPGRADE_AUTH_ERROR_KEY_MATCH', "A chave encontrada ({0}) não confere com a mostrada abaixo, por favor certifique-se de que você forneceu a chave correta.");
define('_PT-BR_UPGRADE_REQ_REQUIREMENT', "Requisito");
define('_PT-BR_UPGRADE_REQ_OPTIONAL', "Opcional e Recomendado");
define('_PT-BR_UPGRADE_REQ_RECOMMENDED', "Recomendado");
define('_PT-BR_UPGRADE_REQ_DIRECTIVE', "Diretiva");
define('_PT-BR_UPGRADE_REQ_ACTUAL', "Atual");
define('_PT-BR_UPGRADE_REQ_RESULT', "Resultado");
define('_PT-BR_UPGRADE_REQ_PHP_VERSION', "versão do PHP");
define('_PT-BR_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_PT-BR_UPGRADE_REQ_DIRECTORY', "diretório {0}");
define('_PT-BR_UPGRADE_REQ_EXTENSION', "extensão {0}");
define('_PT-BR_UPGRADE_REQ_FILE_UPLOAD', "Envio de Arquivos");
define('_PT-BR_UPGRADE_REQ_SAFE_MODE', "Modo seguro");
define('_PT-BR_UPGRADE_REQ_READABLE', "Leitura");
define('_PT-BR_UPGRADE_REQ_WRITABLE', "Escrita");
define('_PT-BR_UPGRADE_REQ_OK', "OK");
define('_PT-BR_UPGRADE_REQ_BAD', "RUIM");
define('_PT-BR_UPGRADE_REQ_OFF', "Off");
define('_PT-BR_UPGRADE_REQ_ON', "On");
define('_PT-BR_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "O diretório {0} não pode ser lido nem escrito, favor corrigir as permissões.");
define('_PT-BR_UPGRADE_REQ_RESPONSE_PHP_VERSION', "A versão do PHP mínima para instalar o Jaws é {0}, portanto você precisa atualizar a sua versão do PHP.");
define('_PT-BR_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Os diretórios listados abaixo como  {0} não podem ser lidos nem escritos, favor corrigir suas permissões.");
define('_PT-BR_UPGRADE_REQ_RESPONSE_EXTENSION', "A extensão {0} é requerida pelo Jaws.");
define('_PT-BR_UPGRADE_DB_INFO', "Você agora precisa configurar uma base de dados, que será usada para armazenar as informações do seu site mais tarde.");
define('_PT-BR_UPGRADE_DB_HOST', "Hostname");
define('_PT-BR_UPGRADE_DB_HOST_INFO', "Se você está indeciso, é seguro deixar como {0}.");
define('_PT-BR_UPGRADE_DB_DRIVER', "Driver");
define('_PT-BR_UPGRADE_DB_USER', "Usuário");
define('_PT-BR_UPGRADE_DB_PASS', "Senha");
define('_PT-BR_UPGRADE_DB_IS_ADMIN', "administrador?");
define('_PT-BR_UPGRADE_DB_NAME', "Nome da base de dados");
define('_PT-BR_UPGRADE_DB_PATH', "Caminho do Banco de Dados");
define('_PT-BR_UPGRADE_DB_PATH_INFO', "Somente preencha o campo se desejar trocar o caminho do driver do SQLite, Interbase ou Firebird.");
define('_PT-BR_UPGRADE_DB_PORT', "Porta");
define('_PT-BR_UPGRADE_DB_PORT_INFO', "Somente preencha este campo se a sua base de dados está rodando em uma porta não padrão. Se você não tem nenhuma idéia em que porta sua base de dados está rodando, então provavelmente é a porta padrão e nós o aconselhamos a deixar este campo em branco.");
define('_PT-BR_UPGRADE_DB_PREFIX', "Prefixo das Tabelas");
define('_PT-BR_UPGRADE_DB_PREFIX_INFO', "Algum prefixo que será colocado em frente ao nome das tabelas, para que você possa rodar mais de um site do Jaws em uma mesma base de dados. Exemplo: blog_");
define('_PT-BR_UPGRADE_DB_RESPONSE_PATH', "Caminho não existe");
define('_PT-BR_UPGRADE_DB_RESPONSE_PORT', "A porta deve ser um valor numérico");
define('_PT-BR_UPGRADE_DB_RESPONSE_INCOMPLETE', "Você precisa preencher todos os campos com exceção da porta e do prefixo das tabelas.");
define('_PT-BR_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Houve um erro ao conectar com o banco de dados. Favor checar as configurações e tentar novamente.");
define('_PT-BR_UPGRADE_REPORT_INFO', "Comparando a versão instalada do Jaws com a atual {0}");
define('_PT-BR_UPGRADE_REPORT_NOTICE', "Você irá encontrar abaixo as versões que podem ser tratadas por este sistema de atualização. Talvez você esteja executando uma versão muito antiga, então nós vamos tomar conta do resto.");
define('_PT-BR_UPGRADE_REPORT_NEED', "Desatualizado");
define('_PT-BR_UPGRADE_REPORT_NO_NEED', "Não necessita de atualização");
define('_PT-BR_UPGRADE_REPORT_NO_NEED_CURRENT', "Não necessita de atualização");
define('_PT-BR_UPGRADE_REPORT_MESSAGE', "Se o atualizador achar que a sua versão do Jaws é antiga, ele irá atualizá-la. Caso contrário, o processo será finalizado.");
define('_PT-BR_UPGRADE_VER_INFO', "Atualizando de {0} para {1}");
define('_PT-BR_UPGRADE_VER_NOTES', "Nota: Assim que você terminar de atualizar sua versão do Jaws, outros gadgets (como Blog, Phoo, etc) vão precisar ser atualizados também. Você pode proceder com esta tarefa entrando no painel de controle.");
define('_PT-BR_UPGRADE_VER_RESPONSE_GADGET_FAILED', "Houve um erro ao instalar o gadget core {0}");
define('_PT-BR_UPGRADE_CONFIG_INFO', "Você precisa agora salvar as suas configurações.");
define('_PT-BR_UPGRADE_CONFIG_SOLUTION', "Você pode fazer isso de duas formas");
define('_PT-BR_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Ajuste a permissão de escrita de {0} e avance esta etapa para que o instalador possa salvar o arquivo para você.");
define('_PT-BR_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Copie o conteúdo da caixa abaixo, cole em um arquivo e salve em {0}");
define('_PT-BR_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Houve um erro desconhecido ao salvar o arquivo de configuração.");
define('_PT-BR_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Você precisa ou ajustar as permissões de escrita do diretório de configurações ou criar o arquivo {0} manualmente.");
define('_PT-BR_UPGRADE_FINISH_INFO', "Você terminou de configurar o seu website!");
define('_PT-BR_UPGRADE_FINISH_CHOICES', "Você pode agora <a href=\"{0}\">ver o seu site</a> ou <a href=\"{1}\">entrar no painel de controle</a>.");
define('_PT-BR_UPGRADE_FINISH_MOVE_LOG', "Nota: Se você ativou o log do processo de instalação, sugerimos que você remova ou mova o arquivo agora.");
define('_PT-BR_UPGRADE_FINISH_THANKS', "Obrigado por escolher o Jaws!");
