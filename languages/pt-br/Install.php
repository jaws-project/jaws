<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Igor Feghali <ifeghali@php.net>"
 * "Language-Team: PT-BR"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_PT-BR_INSTALL_INTRODUCTION', "Introdução");
define('_PT-BR_INSTALL_AUTHENTICATION', "Autenticação");
define('_PT-BR_INSTALL_REQUIREMENTS', "Requisitos");
define('_PT-BR_INSTALL_DATABASE', "Base de Dados");
define('_PT-BR_INSTALL_CREATEUSER', "Criar Um Usuário");
define('_PT-BR_INSTALL_SETTINGS', "Configurações");
define('_PT-BR_INSTALL_WRITECONFIG', "Salvar Configuração");
define('_PT-BR_INSTALL_FINISHED', "Finalizar");
define('_PT-BR_INSTALL_INTRO_WELCOME', "Bem-vindo à instalação do Jaws.");
define('_PT-BR_INSTALL_INTRO_INSTALLER', "Você será guiado passo-a-passo para instalação do seu website. Por favor certifique-se de que você tem disponível");
define('_PT-BR_INSTALL_INTRO_DATABASE', "Os detalhes do banco de dados - endereço, usuário, senha, nome da base de dados.");
define('_PT-BR_INSTALL_INTRO_FTP', "Uma forma de enviar arquivos, provavelmente FTP.");
define('_PT-BR_INSTALL_INTRO_MAIL', "Informações sobre o seu servidor de mensagens (endereço, usuário, senha), se você irá usar um carteiro.");
define('_PT-BR_INSTALL_INTRO_LOG', "Logar o processo de instalação no arquivo {0}");
define('_PT-BR_INSTALL_INTRO_LOG_ERROR', "Nota: se você desejar logar o processo de instalação, primeiro conceda ao servidor web permissão de escrita para o diretório {0}, e então atualize esta página");
define('_PT-BR_INSTALL_AUTH_PATH_INFO', "Para garantir que você é realmente o administrador deste site, por favor crie um arquivo chamado {0} no diretório de instalação do Jaws ({1}).");
define('_PT-BR_INSTALL_AUTH_UPLOAD', "Você pode enviar o arquivo para o servidor da mesma forma que enviou a sua cópia do Jaws.");
define('_PT-BR_INSTALL_AUTH_KEY_INFO', "O arquivo deve conter o código mostrado abaixo, e nada mais.");
define('_PT-BR_INSTALL_AUTH_ENABLE_SECURITY', "Ativar instalação segura (Powered by RSA)");
define('_PT-BR_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Houve um erro ao gerar a chave RSA. Por favor tente novamente.");
define('_PT-BR_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Houve um erro ao gerar a chave RSA. Nenhuma extensão matemática está disponível.");
define('_PT-BR_INSTALL_AUTH_ERROR_KEY_FILE', "O arquivo de chave ({0}) não foi encontrado, por favor certifique-se de que você o criou e que o servidor web tem permissão de leitura sobre ele.");
define('_PT-BR_INSTALL_AUTH_ERROR_KEY_MATCH', "A chave encontrada ({0}) não confere com a mostrada abaixo, por favor certifique-se de que você forneceu a chave correta.");
define('_PT-BR_INSTALL_REQ_REQUIREMENT', "Requisito");
define('_PT-BR_INSTALL_REQ_OPTIONAL', "Opcional e Recomendado");
define('_PT-BR_INSTALL_REQ_RECOMMENDED', "Recomendado");
define('_PT-BR_INSTALL_REQ_DIRECTIVE', "Diretiva");
define('_PT-BR_INSTALL_REQ_ACTUAL', "Atual");
define('_PT-BR_INSTALL_REQ_RESULT', "Resultado");
define('_PT-BR_INSTALL_REQ_PHP_VERSION', "versão do PHP");
define('_PT-BR_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_PT-BR_INSTALL_REQ_DIRECTORY', "diretório {0}");
define('_PT-BR_INSTALL_REQ_EXTENSION', "extensão {0}");
define('_PT-BR_INSTALL_REQ_FILE_UPLOAD', "Envio de Arquivos");
define('_PT-BR_INSTALL_REQ_SAFE_MODE', "Modo seguro");
define('_PT-BR_INSTALL_REQ_READABLE', "Leitura");
define('_PT-BR_INSTALL_REQ_WRITABLE', "Escrita");
define('_PT-BR_INSTALL_REQ_OK', "OK");
define('_PT-BR_INSTALL_REQ_BAD', "RUIM");
define('_PT-BR_INSTALL_REQ_OFF', "Off");
define('_PT-BR_INSTALL_REQ_ON', "On");
define('_PT-BR_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "O diretório {0} não pode ser lido nem escrito, favor corrigir as permissões.");
define('_PT-BR_INSTALL_REQ_RESPONSE_PHP_VERSION', "A versão do PHP mínima para instalar o Jaws é {0}, portanto você precisa atualizar a sua versão do PHP.");
define('_PT-BR_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Os diretórios listados abaixo como  {0} não podem ser lidos nem escritos, favor corrigir suas permissões.");
define('_PT-BR_INSTALL_REQ_RESPONSE_EXTENSION', "A extensão {0} é requerida pelo Jaws.");
define('_PT-BR_INSTALL_DB_INFO', "Você agora precisa configurar uma base de dados, que será usada para armazenar as informações do seu site mais tarde.");
define('_PT-BR_INSTALL_DB_NOTICE', "A base de dados fornecida já precisa estar criada para que o processo funcione.");
define('_PT-BR_INSTALL_DB_HOST', "Hostname");
define('_PT-BR_INSTALL_DB_HOST_INFO', "Se você está indeciso, é seguro deixar como {0}.");
define('_PT-BR_INSTALL_DB_DRIVER', "Driver");
define('_PT-BR_INSTALL_DB_USER', "Usuário");
define('_PT-BR_INSTALL_DB_PASS', "Senha");
define('_PT-BR_INSTALL_DB_IS_ADMIN', "administrador?");
define('_PT-BR_INSTALL_DB_NAME', "Nome da base de dados");
define('_PT-BR_INSTALL_DB_PATH', "Caminho do Banco de Dados");
define('_PT-BR_INSTALL_DB_PATH_INFO', "Somente preencha o campo se desejar trocar o caminho do driver do SQLite, Interbase ou Firebird.");
define('_PT-BR_INSTALL_DB_PORT', "Porta");
define('_PT-BR_INSTALL_DB_PORT_INFO', "Somente preencha este campo se a sua base de dados está rodando em uma porta não padrão. Se você não tem nenhuma idéia em que porta sua base de dados está rodando, então provavelmente é a porta padrão e nós o aconselhamos a deixar este campo em branco.");
define('_PT-BR_INSTALL_DB_PREFIX', "Prefixo das Tabelas");
define('_PT-BR_INSTALL_DB_PREFIX_INFO', "Algum prefixo que será colocado em frente ao nome das tabelas, para que você possa rodar mais de um site do Jaws em uma mesma base de dados. Exemplo: blog_");
define('_PT-BR_INSTALL_DB_RESPONSE_PATH', "Caminho não existe");
define('_PT-BR_INSTALL_DB_RESPONSE_PORT', "A porta deve ser um valor numérico");
define('_PT-BR_INSTALL_DB_RESPONSE_INCOMPLETE', "Você precisa preencher todos os campos com exceção da porta e do prefixo das tabelas.");
define('_PT-BR_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Houve um erro ao conectar com o banco de dados. Favor checar as configurações e tentar novamente.");
define('_PT-BR_INSTALL_DB_RESPONSE_GADGET_INSTALL', "Houve um erro ao instalar o gadget core {0}");
define('_PT-BR_INSTALL_DB_RESPONSE_SETTINGS', "Houve um erro ao configurar o banco de dados.");
define('_PT-BR_INSTALL_USER_INFO', "Você pode agora criar uma conta de usuário para você.");
define('_PT-BR_INSTALL_USER_NOTICE', "Lembre-se de não escolher uma senha fácil de advinhar visto que qualquer pessoa de posse da sua senha tem o controle total sobre o seu site.");
define('_PT-BR_INSTALL_USER_USER', "Usuário");
define('_PT-BR_INSTALL_USER_USER_INFO', "Seu nome de login, que será exibido pelos registros que você criar.");
define('_PT-BR_INSTALL_USER_PASS', "Senha");
define('_PT-BR_INSTALL_USER_REPEAT', "Repita");
define('_PT-BR_INSTALL_USER_REPEAT_INFO', "Repita a senha para assegurar que não existe engano.");
define('_PT-BR_INSTALL_USER_NAME', "Nome");
define('_PT-BR_INSTALL_USER_NAME_INFO', "Seu nome real.");
define('_PT-BR_INSTALL_USER_EMAIL', "Endereço de Email");
define('_PT-BR_INSTALL_USER_RESPONSE_PASS_MISMATCH', "A senha e a verificação não combinam, por favor tente novamente.");
define('_PT-BR_INSTALL_USER_RESPONSE_INCOMPLETE', "Você deve completar o nome de usuário, a senha e o campo de verificação.");
define('_PT-BR_INSTALL_USER_RESPONSE_CREATE_FAILED', "Houve um erro ao criar seu usuário.");
define('_PT-BR_INSTALL_SETTINGS_INFO', "Você pode agora ajustar as configurações padrão do seu site. Você poderá modificar qualquer uma delas posteriormente, entrando no Painel de Controle e selecionando Configurações.");
define('_PT-BR_INSTALL_SETTINGS_SITE_NAME', "Nome do Site");
define('_PT-BR_INSTALL_SETTINGS_SITE_NAME_INFO', "O nome a ser exibido no seu site.");
define('_PT-BR_INSTALL_SETTINGS_SLOGAN', "Descrição");
define('_PT-BR_INSTALL_SETTINGS_SLOGAN_INFO', "Uma descrição detalhada do site.");
define('_PT-BR_INSTALL_SETTINGS_DEFAULT_GADGET', "Gadget Padrão");
define('_PT-BR_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "O gadget a ser exibido quando alguém entrar no site.");
define('_PT-BR_INSTALL_SETTINGS_SITE_LANGUAGE', "Idioma do Site");
define('_PT-BR_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "O idioma padrão a ser utilizado no site.");
define('_PT-BR_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Você deve preencher o campo de nome do site.");
define('_PT-BR_INSTALL_CONFIG_INFO', "Você precisa agora salvar as suas configurações.");
define('_PT-BR_INSTALL_CONFIG_SOLUTION', "Você pode fazer isso de duas formas");
define('_PT-BR_INSTALL_CONFIG_SOLUTION_PERMISSION', "Ajuste a permissão de escrita de {0} e avance esta etapa para que o instalador possa salvar o arquivo para você.");
define('_PT-BR_INSTALL_CONFIG_SOLUTION_UPLOAD', "Copie o conteúdo da caixa abaixo, cole em um arquivo e salve em {0}");
define('_PT-BR_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Houve um erro desconhecido ao salvar o arquivo de configuração.");
define('_PT-BR_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Você precisa ou ajustar as permissões de escrita do diretório de configurações ou criar o arquivo {0} manualmente.");
define('_PT-BR_INSTALL_FINISH_INFO', "Você terminou de configurar o seu website!");
define('_PT-BR_INSTALL_FINISH_CHOICES', "Você pode agora <a href=\"{0}\">ver o seu site</a> ou <a href=\"{1}\">entrar no painel de controle</a>.");
define('_PT-BR_INSTALL_FINISH_MOVE_LOG', "Nota: Se você ativou o log do processo de instalação, sugerimos que você remova ou mova o arquivo agora.");
define('_PT-BR_INSTALL_FINISH_THANKS', "Obrigado por escolher o Jaws!");
