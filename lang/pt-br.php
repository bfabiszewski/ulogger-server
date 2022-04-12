<?php
/* μlogger
 *
 * Copyright(C) 2022 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

// default language for translations

// strings only used in setup
$langSetup["dbconnectfailed"] = "Falha ao conectar com a base de dados.";
$langSetup["serversaid"] = "Resposta do servidor: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Por favor, verifique as configurações de base de dados no arquivo 'config.php'.";
$langSetup["dbqueryfailed"] = "A consulta com base de dados falhou.";
$langSetup["dbtablessuccess"] = "Tabelas criadas com sucesso na base de dados!";
$langSetup["setupuser"] = "Agora, por favor, defina seu usuário no µlogger.";
$langSetup["congratulations"] = "Parabéns!";
$langSetup["setupcomplete"] = "A configuração foi concluída. Você pode ir agora ao <a href=\"../index.php\">site principal</a> e fazer login com sua nova conta de usuário.";
$langSetup["disablewarn"] = "IMPORTANTE! VOCÊ DEVE DESATIVAR O SCRIPT 'setup.php' OU REMOVÊ-LO DE SEU SERVIDOR.";
$langSetup["disabledesc"] = "Ao deixar este script acessível pelo navegador você se expõe a um grande risco de segurança. Qualquer pessoa poderá executá-lo, remover sua base de dados e criar uma nova conta de usuário. Exclua ou desabilite o arquivo, ajustando o valor %s de volta para %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Infelizmente, algo deu errado. Você pode tentar encontrar mais detalhes em registros do servidor.";
$langSetup["welcome"] = "Bem-vindo ao µlogger!";
$langSetup["disabledwarn"] = "Por razões de segurança este script é desativado por padrão. Para ativá-lo, você deve editar o arquivo 'scripts/setup.php' via editor de texto e definir a variável %s no início do arquivo como %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Linha: %s deve ser alterada para: %s";
$langSetup["dorestart"] = "Por favor, reinicie este script quando tiver terminado.";
$langSetup["createconfig"] = "Por favor crie o arquivo 'config.php' na pasta root. Você pode começar copiando-o do 'config.default.php'. Certifique-se de ajustar os valores de configuração para corresponder às suas necessidades e à configuração de sua base de dados.";
$langSetup["nodbsettings"] = "Você deve fornecer suas credenciais de base de dados no arquivo 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Este script configurará as tabelas necessárias para µlogger (%s). Elas serão criadas na sua base de dados chamada %s. Aviso, se as tabelas já existirem serão descartadas e recriadas, seu conteúdo será destruído."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Quando terminado o script solicitará um novo nome de usuário e a senha para criar novo usuário do µlogger..";
$langSetup["startbutton"] = "Pressione para começar";
$langSetup["restartbutton"] = "Reiniciar";
$langSetup["optionwarn"] = "Opção de configuração PHP %s deve ser definida como %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Extensão PHP necessária %s não está disponível."; // substitutes extension name
$langSetup["notwritable"] = "Pasta '%s' deve ter permissão de escrever para PHP."; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Você precisa de login e a senha para acessar esta página.";
$lang["authfail"] = "Nome de usuário ou senha incorreta";
$lang["user"] = "Usuário";
$lang["track"] = "Caminho";
$lang["latest"] = "última posição";
$lang["autoreload"] = "auto-atualizar";
$lang["reload"] = "Atualizar agora";
$lang["export"] = "Exportar caminho";
$lang["chart"] = "Gráfico de altitudes";
$lang["close"] = "fechar";
$lang["time"] = "Tempo";
$lang["speed"] = "Velocidade";
$lang["accuracy"] = "Precisão";
$lang["position"] = "Posição";
$lang["altitude"] = "Altitude";
$lang["bearing"] = "Rumo";
$lang["ttime"] = "Tempo total";
$lang["aspeed"] = "Velocidade média";
$lang["tdistance"] = "Distância total";
$lang["pointof"] = "Ponto %d de %d"; // e.g. Point 3 of 10
$lang["summary"] = "Resumo";
$lang["suser"] = "selecione usuário";
$lang["logout"] = "Sair";
$lang["login"] = "Entrar";
$lang["username"] = "Nome de usuário";
$lang["password"] = "Senha";
$lang["language"] = "Idioma";
$lang["newinterval"] = "Digite novo valor para intervalo (segundos)";
$lang["api"] = "API da mapa";
$lang["units"] = "Unidades";
$lang["metric"] = "Métricas";
$lang["imperial"] = "Imperais/EUA";
$lang["nautical"] = "Náuticas";
$lang["admin"] = "Admin";
$lang["adminmenu"] = "Gerenciar";
$lang["passwordrepeat"] = "Repita a senha";
$lang["passwordenter"] = "Digite a senha";
$lang["usernameenter"] = "Digite o nome de usuário";
$lang["adduser"] = "Adicionar usuário";
$lang["userexists"] = "Usuário já existe";
$lang["cancel"] ="Cancelar";
$lang["submit"] = "Enviar";
$lang["oldpassword"] = "Senha antiga";
$lang["newpassword"] = "Nova senha";
$lang["newpasswordrepeat"] = "Repita a nova senha";
$lang["changepass"] = "Alterar a senha";
$lang["gps"] = "GPS";
$lang["network"] = "Rede";
$lang["deluser"] = "Remover usuário";
$lang["edituser"] = "Editar usuário";
$lang["servererror"] = "Erro no servidor";
$lang["allrequired"] = "Todos os campos são obrigatórios";
$lang["passnotmatch"] = "As senhas não correspondem";
$lang["oldpassinvalid"] = "Senha antiga incorreta";
$lang["passempty"] = "Senha em branco";
$lang["loginempty"] = "Login em branco";
$lang["passstrengthwarn"] = "A senha muito fraca";
$lang["actionsuccess"] = "Ação concluída com sucesso";
$lang["actionfailure"] = "Algo deu errado";
$lang["notauthorized"] = "Usuário não autorizado";
$lang["userunknown"] = "Usuário desconhecido";
$lang["userdelwarn"] = "Aviso!\n\nVocê vai permanentemente excluir usuário %, junto com todos seus caminhos e posições.\n\nTem certeza?"; // substitutes user login
$lang["editinguser"] = "Você está editando usuário %s"; // substitutes user login
$lang["selfeditwarn"] = "Você não pode editar seu próprio usuário com esta ferramenta";
$lang["apifailure"] = "Desculpe, não posso carregar %s API"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Aviso!\n\nVocê vai permanentemente excluir o caminho %s e todas suas posições.\n\nTem certeza?"; // substitutes track name
$lang["editingtrack"] = "Você está editando o caminho %s"; // substitutes track name
$lang["deltrack"] = "Remover caminho";
$lang["trackname"] = "Nome do caminho";
$lang["edittrack"] = "Editar caminho";
$lang["positiondelwarn"] = "Aviso!\n\nVocê vai permanentemente excluir a posição %d do caminho %s.\n\nTem certeza?"; // substitutes position index and track name
$lang["editingposition"] = "Você está editando a posição #%d do caminho %s"; // substitutes position index and track name
$lang["delposition"] = "Remover posição";
$lang["delimage"] = "Remover imagem";
$lang["comment"] = "Comentário";
$lang["image"] = "Imagem";
$lang["editposition"] = "Editar posição";
$lang["passlenmin"] = "A senha deve ter pelo menos %d caracteres"; // substitutes password minimum length
$lang["passrules_1"] = "Deve conter pelo menos uma letra minúscula, uma letra maiúscula";
$lang["passrules_2"] = "Deve conter pelo menos uma letra minúscula, uma letra maiúscula e um dígito";
$lang["passrules_3"] = "Deve conter pelo menos uma letra minúscula, uma letra maiúscula, um dígito e um caracter não alfanumérico";
$lang["owntrackswarn"] = "Você só pode editar seus próprios caminhos";
$lang["gmauthfailure"] = "Pode haver um problema com a chave API do Google Maps nesta página";
$lang["gmapilink"] = "Você pode encontrar mais detalhes sobre as chaves API neste <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">site do Google</a>";
$lang["import"] = "Importar caminho";
$lang["iuploadfailure"] = "O envio falhou";
$lang["iparsefailure"] = "Erro ao analisar o arquivo";
$lang["idatafailure"] = "Nenhum dado de caminho no arquivo importado";
$lang["isizefailure"] = "Tamanho do arquivo carregado não deve exceder %d bytes"; // substitutes number of bytes
$lang["imultiple"] = "Aviso, múltiplos caminhos importados (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Todos usuários";
$lang["unitday"] = "d"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "km/h"; // kilometer per hour
$lang["unitm"] = "m"; // meter
$lang["unitamsl"] = "a.n.m."; // above mean see level
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "ft"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Configuração";
$lang["editingconfig"] = "Configurações padrão do app";
$lang["latitude"] = "Latitude inicial";
$lang["longitude"] = "Longitude inicial";
$lang["interval"] = "Intervalo (s)";
$lang["googlekey"] = "Chave API do Google Maps";
$lang["passlength"] = "Tamanho mínimo da senha";
$lang["passstrength"] = "Força mínima da senha";
$lang["requireauth"] = "Necessária autorização";
$lang["publictracks"] = "Caminhos públicos";
$lang["strokeweight"] = "Grossura de linha";
$lang["strokeopacity"] = "Opacidade de linha";
$lang["strokecolor"] = "Cor de linha";
$lang["colornormal"] = "Cor de marcador";
$lang["colorstart"] = "Cor de marcador na partida";
$lang["colorstop"] = "Cor de marcador na parada";
$lang["colorextra"] = "Cor extra de marcador";
$lang["colorhilite"] = "Cor de marcador no destaque";
$lang["uploadmaxsize"] = "Tamanho máximo de envio (MB)";
$lang["ollayers"] = "Camada OpenLayers";
$lang["layername"] = "Nome da camada";
$lang["layerurl"] = "URL da camada";
$lang["add"] = "Adicionar";
$lang["edit"] = "Editar";
$lang["delete"] = "Excluir";
$lang["settings"] = "Configurações";
$lang["trackcolor"] = "Cor de caminho";
?>
