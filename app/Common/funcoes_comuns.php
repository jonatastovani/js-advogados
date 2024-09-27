<?php

//Retorna uma string com os ESTADOS(UF) existentes para ser inserido no select
function inserirUF($idSelecionar=0){
    include_once "configuracoes/conexao.php";

    $sql = "SELECT ID VALOR, SIGLA NOMEEXIBIR FROM tab_estados ORDER BY SIGLA;";
    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetchAll();
        $retorno = "<option value=0>UF</option>";
        foreach($resultado as $dados){
            if($dados['VALOR']==$idSelecionar){
                $retorno .= "<option value=".$dados['VALOR']." selected>".$dados['NOMEEXIBIR']."</option>";
            }else{
                $retorno .= "<option value=".$dados['VALOR'].">".$dados['NOMEEXIBIR']."</option>";
            }
        }
    }else{
        $retorno = "<option value='0'>".$conexaoStatus."</option>";
    }
    return $retorno;
    //unset($GLOBALS['conexao']);
}

//Retorna uma string com as nacionalidades existentes para ser inserido no select
function inserirListaSimples($tipo,$idSelecionar = 0){
    include_once "configuracoes/conexao.php";
    $where = '';
    
    switch ($tipo){
        case 1: $tabela = "tab_nacionalidade";
        break;
        case 2: $tabela = "tab_profissoes";
        break;
        case 3: $tabela = "tab_instrucao";
        break;
        case 4: $tabela = "tab_civil";
        break;
        case 5: $tabela = "tab_religiao";
        break;
        case 6: $tabela = "tab_cutis";
        break;
        case 7: $tabela = "tab_cabelotipo";
        break;
        case 8: $tabela = "tab_cabelocor";
        break;
        case 9: $tabela = "tab_olhos";
        break;
        case 10: $tabela = "tab_cidades";
        break;
        case 11: $tabela = "tab_grauparentesco";
        break;
        case 12: 
            $tabela = "inc_pertencestipopertence";
            $where = "WHERE ID > 1";
        break;
    }

    $sql = "SELECT ID VALOR, NOME NOMEEXIBIR FROM $tabela $where ORDER BY NOME;";
    $retorno = "";
    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetchAll();
        $retorno = "<option value=0>Selecione</option>";
        foreach($resultado as $dados){
            if($dados['VALOR']==$idSelecionar){
                $retorno .= "<option value=".$dados['VALOR']." selected>".$dados['NOMEEXIBIR']."</option>";
            }else{
                $retorno .= "<option value=".$dados['VALOR'].">".$dados['NOMEEXIBIR']."</option>";
            }
        }
    }else{
        $retorno = "<option value='0'>".$conexaoStatus."</option>";
    }
    return $retorno;
    //unset($GLOBALS['conexao']);
}

//Retorna uma string com as nacionalidades existentes para ser inserido no select
//tipo 1 = Busca somente presos que estão na unidade
//tipo 2 = Busca somente presos que não estão na unidade
//tipo 3 = Busca todos os presos que já passaram aqui (não é recomendado usar essa opção pois pode demorar o carregamento)
function inserirPresos($tipo){
    include_once "configuracoes/conexao.php";
    $where = '';

    //Busca somente presos que estão na unidade
    if($tipo==1){
        $where = "WHERE IDPRESO <> 0;";
    }
    //Busca somente presos que não estão na unidade
    elseif($tipo==2){
        $where = "WHERE IDPRESO = 0;";
    }
    //Busca todos os presos que já passaram aqui (não é recomendado usar essa opção pois pode demorar o carregamento)
    elseif($tipo==3){
        $where = '';
    }

    $sql = "SELECT MATRICULA VALOR, CONCAT(MID(MATRICULA, 1, LENGTH(MATRICULA)-1), '-', MID(MATRICULA, LENGTH(MATRICULA), 1), ' - ', NOME) NOMEEXIBIR FROM cadastros $where";

    $retorno = "";
    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetchAll();
        foreach($resultado as $dados){
            $retorno = $retorno . "<option value=".$dados['VALOR'].">".$dados['NOMEEXIBIR']."</option>";
        }
    }else{
        $retorno = "<option value='0'>".$conexaoStatus."</option>";
    }
    return $retorno;
    //unset($GLOBALS['conexao']);
}

//Retorna uma string com as Entradas existentes para ser inserido no select
function inserirEntradas(){
    include_once "configuracoes/conexao.php";

    $sql = "SELECT E.ID VALOR, concat(E.ID, ' - ', date_format(E.DATAENTRADA,'%d/%m/%Y %H:%i'), ' (', GSA.NOME,')') NOMEEXIBIR FROM entradas E 
    INNER JOIN codigo_gsa GSA ON E.IDORIGEM = GSA.ID
    WHERE E.ID > 0 AND E.IDEXCLUSOREGISTRO IS NULL AND E.DATAEXCLUSOREGISTRO IS NULL ORDER BY E.DATAENTRADA DESC";
    $retorno = "";
    
    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetchAll();
        foreach($resultado as $dados){
            $retorno = $retorno . "<option value=".$dados['VALOR'].">".$dados['NOMEEXIBIR']."</option>";
        }
    }else{
        $retorno = "<option value='0'>".$conexaoStatus."</option>";
    }
    return $retorno;
    //unset($GLOBALS['conexao']);
}

//Verifica se o usuário tem a permissão necessária para efetuar alguma ação ou acessar algum setor
//$permissoesNecessarias = Array de permissões necessárias
//$redirecionamento = string de redirecionamento caso não há a permissão necessária;
function verificaPermissao($permissoesNecessarias,$redirecionamento=""){
    // var_dump($permissoesNecessarias);

    $permissoesNecessarias = buscaPermissoesPai($permissoesNecessarias,2);
    array_push($permissoesNecessarias,1);

    $idusuario = $_SESSION['id_usuario'];
    $resultadousuario = buscaPermissoesUsuario($idusuario,2);
    $resultadoposto = buscaPermissoesPostoTrabalho($idusuario,2);

    $permissoes=[];
    if(count($resultadousuario) && count($resultadoposto)){
        $permissoes = array_merge($resultadousuario,$resultadoposto);
    }elseif(count($resultadoposto)){
        $permissoes = $resultadoposto;
    }elseif(count($resultadousuario)){
        $permissoes = $resultadousuario;
    }

    if(count($permissoes)){
        $permissoes = buscaPermissoesFilhas($permissoes,2,true);
    }

    // var_dump($permissoes);

    if(is_array($permissoes)){
        foreach($permissoes as $permissao){
            if(in_array($permissao,$permissoesNecessarias)){
                return true;
                exit();
            }
        }
        if($redirecionamento!=""){
            header("Location: $redirecionamento");
            exit();
        }
        return false;
        exit();

    }else{
        echo $permissoes;
        return false;
        exit();
    }
}

//Verifica se o computador tem a permissão necessária para efetuar alguma ação ou acessar algum setor
//$permissoesNecessarias = Array de permissões necessárias
//$redirecionamento = string de redirecionamento caso não há a permissão necessária;
function verificaPermissaoComputador($permissoesNecessarias,$redirecionamento=""){
    include_once "userinfo.php";
    $ipcomputador = UserInfo::get_ip();

    $permissoesNecessarias = buscaPermissoesPai($permissoesNecessarias,2);
    array_push($permissoesNecessarias,1);

    $retorno = buscaPermissoesComputador($ipcomputador,2);
    if(count($retorno)){
        $retorno = buscaPermissoesFilhas($retorno,2,true);
    }

    if(is_array($retorno)){
        foreach($retorno as $permissao){
            if(in_array($permissao,$permissoesNecessarias)){
                return true;
                exit();
            }
        }
        if($redirecionamento!=""){
            header("Location: $redirecionamento");
            exit();
        }
        return false;
        exit();

    }else{
        echo $retorno;
        return false;
        exit();
    }
}

//Busca as permissões atribuídas a este usuário;
// $idusuario = ID do usuário que está se buscando as permissões
// $tiporetorno 1 = retorna o array completo da consulta
// $tiporetorno 2 = retorna somente os IDPERMISSAO em um array
function buscaPermissoesUsuario($idusuario, $tiporetorno){

    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){

        $sql = retornaQueryDadosBoletimVigente();
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        
        $sql = "SELECT * FROM tab_usuariospermissoes WHERE IDUSUARIO = :idusuario AND (DATAINICIO IS NULL AND DATATERMINO IS NULL OR DATAINICIO <= @dataBoletimCurta AND DATATERMINO IS NULL OR DATAINICIO <= @dataBoletimCurta AND DATATERMINO >= @dataBoletimCurta) AND IDEXCLUSOREGISTRO IS NULL;";
    
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->bindParam('idusuario', $idusuario,PDO::PARAM_INT);
        $stmt->execute();
        //unset($GLOBALS['conexao']);
        
        $resultado = $stmt->fetchAll();
        if($tiporetorno==1){
            return $resultado;
        }elseif($tiporetorno==2){
            $retorno = [];
            foreach($resultado as $permissao){
                array_push($retorno,$permissao['IDPERMISSAO']);
            }
            return $retorno;
        }
    }else{
        return $conexaoStatus;
    }
}

//Busca as permissões concedidas ao computador;
// $ipcomputador = IP do computador que está se buscando as permissões
// $tiporetorno 1 = retorna o array completo da consulta
// $tiporetorno 2 = retorna somente os IDPERMISSAO em um array
function buscaPermissoesComputador($ipcomputador, $tiporetorno){

    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){

        $sql = retornaQueryDadosBoletimVigente();
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        
        $params = [$ipcomputador];

        $sql = "SELECT * FROM tab_computadores_aute WHERE IP = ?;";
    
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute($params);
        
        $resultado = $stmt->fetchAll();
        if($tiporetorno==1){
            return $resultado;
        }elseif($tiporetorno==2){
            $retorno = [];
            foreach($resultado as $permissao){
                array_push($retorno,$permissao['IDPERMISSAO']);
            }
            return $retorno;
        }
    }else{
        return $conexaoStatus;
    }
}

//Busca as permissões atribuídas ao posto de trabalho;
// $idusuario = ID do usuário que está se buscando as permissões
// $tiporetorno 1 = retorna o array completo da consulta
// $tiporetorno 2 = retorna somente os IDPERMISSAO em um array
function buscaPermissoesPostoTrabalho($idusuario, $tiporetorno){

    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){

        $sql = retornaQueryDadosBoletimVigente();
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        
        $params = [$idusuario];

        $sql = "SELECT FEPP.* FROM funcionarios_escalaplantao_func FEPF
        INNER JOIN funcionarios_escalaplantao FEP ON FEP.ID = FEPF.IDESCALA
        INNER JOIN funcionarios_escalapostos_perm FEPP ON FEPP.IDPOSTO = FEPF.IDPOSTO
        WHERE FEP.IDBOLETIM = @intIDBoletim AND FEPF.IDUSUARIO = ? AND FEPF.IDEXCLUSOREGISTRO IS NULL AND FEP.IDEXCLUSOREGISTRO IS NULL;";

        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute($params);
        //unset($GLOBALS['conexao']);
        
        $resultado = $stmt->fetchAll();
        if($tiporetorno==1){
            return $resultado;
        }elseif($tiporetorno==2){
            $retorno = [];
            foreach($resultado as $permissao){
                array_push($retorno,$permissao['IDPERMISSAO']);
            }
            return $retorno;
        }
    }else{
        return $conexaoStatus;
    }
}

//Busca as permissões pais acima da permissão informada;
// $arrpermissoes = array de permissões mínimas
// $tiporetorno 1 = retorna o array completo da consulta
// $tiporetorno 2 = retorna somente os IDPERMISSAO em um array
function buscaPermissoesPai($arrpermissoes, $tiporetorno){

    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        //Cria a tabela temporária que irá armazenar as permissões superiores das informadas

        $sql = "CREATE TEMPORARY TABLE IF NOT EXISTS temp_permissoes (
            ID INT auto_increment, primary key (ID),
            IDPERMISSAO INT NOT NULL,
            NOME varchar(50) NOT NULL,
            DESCRICAO tinytext NOT NULL,
            IDSETORPAI INT NOT NULL,
            IDGRUPO INT NOT NULL,
            ORDEM INT NOT NULL) default char set UTF8;";

        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
                        
        $blnsair = false;
        while($blnsair!=true){

            $where = '';
            $params = [];

            foreach($arrpermissoes as $idperm){
                if($where==''){
                    $where = '?';
                }else{
                    $where .= ', ?';
                }
                array_push($params,$idperm);
            }

            //Monta a consulta para buscar dados desta permissão e obter o IDSETORPAI
            $sql = "SELECT ID, NOME, DESCRICAO, IDSETORPAI, IDGRUPO, ORDEM FROM tab_permissoes
            WHERE ID IN ($where);";

            $stmt = $GLOBALS['conexao']->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetchAll();

            // Se retornar consulta então se faz uma consulta recursiva, retornando até adicionar todos as permissões que este usuário pode gerenciar
            if(count($resultado)>0){

                //Coloca as novas permissões para buscar recursivamente dentro do while
                $arrpermissoes=[];
                foreach($resultado as $permissao){
                    array_push($arrpermissoes,$permissao['IDSETORPAI']);
                }

                $sql = "INSERT INTO temp_permissoes (IDPERMISSAO, NOME, DESCRICAO, IDSETORPAI, IDGRUPO, ORDEM) $sql";

                $stmt = $GLOBALS['conexao']->prepare($sql);
                $stmt->execute($params);
                $resultado = $stmt->fetchAll();
                
            }else{
                $blnsair = true;
            }
        }
        
        $sql = "SELECT TMP.*, PERMG.NOME NOMEGRUPO FROM temp_permissoes TMP
        INNER JOIN tab_permissoesgrupo PERMG ON PERMG.ID = TMP.IDGRUPO
        ORDER BY IDGRUPO, IDSETORPAI, ORDEM;";

        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();            
        $resultado = $stmt->fetchAll();
        
        if($tiporetorno==1){
            return $resultado;
        }elseif($tiporetorno==2){
            $retorno = [];
            foreach($resultado as $permissao){
                array_push($retorno,$permissao['IDPERMISSAO']);
            }
            return $retorno;
        }
    }else{
        return $conexaoStatus;
    }
}

//Busca as permissões filhas abaixo da permissão informada;
// $arrpermissoes = array de permissões máximas
// $tiporetorno 1 = retorna o array completo da consulta
// $tiporetorno 2 = retorna somente os IDPERMISSAO em um array
// $blnincluirpermissaopai = true => incluir a permissão pai na listagem final (Ex: Quando o usuário pode acessar um local com requisito mínimo sendo a permissão dele.)
// $blnincluirpermissaopai = false => não incluir a permissão pai na listagem final (Ex: Quando somente retornar as permissões que as permissões informadas podem gerenciar.)
function buscaPermissoesFilhas($arrpermissoes, $tiporetorno,$blnincluirpermissaopai=false){

    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        //Cria a tabela temporária que irá armazenar as permissões que o usuário pode editar
        $sql = "CREATE TEMPORARY TABLE IF NOT EXISTS temp_permissoes (
            ID INT auto_increment, primary key (ID),
            IDPERMISSAO INT NOT NULL,
            NOME varchar(50) NOT NULL,
            DESCRICAO tinytext NOT NULL,
            IDSETORPAI INT NOT NULL,
            IDGRUPO INT NOT NULL,
            ORDEM INT NOT NULL,
            DIRETOR BOOL NOT NULL DEFAULT FALSE,
            INDIVIDUAIS BOOL NOT NULL DEFAULT FALSE
            ) default char set UTF8;";

        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
                        
        $blnsair = false;
        $contadorincluir = 0;
        $arrpermissoesinicio = [];

        //Se for pra incluir as permissões informadas no início, então se reserva os dados para incluir no final
        if($contadorincluir==0 && $blnincluirpermissaopai==true){
            $arrpermissoesinicio = $arrpermissoes;
        }
        
        while($blnsair!=true){

            $where = '';
            $params = [];
            $arr = [];

            for($i=0;$i<2;$i++){
                foreach($arrpermissoes as $perm){
                    if($where==''){
                        $where = '?';
                            // $sqlwhere = "$perm";
                        }else{
                        $where .= ', ?';
                            // $sqlwhere = "$perm";
                        }
                    array_push($params,$perm);
                }

                // echo $sqlwhere ;
                array_push($arr,$where);
                $where = '';
            }

            // echo __LINE__." ".$arr[0];
            
            //Monta a consulta para ver se algum dos IDS é um setor pai, se for um setor pai vai retornar uma consulta
            $sql = "SELECT PERM.ID, PERM.NOME, PERM.DESCRICAO, PERM.IDSETORPAI, PERM.IDGRUPO, PERM.ORDEM, PERM.DIRETOR, PERMGRUPO.INDIVIDUAIS
            FROM tab_permissoes PERM
            INNER JOIN tab_permissoesgrupo PERMGRUPO ON PERMGRUPO.ID = PERM.IDGRUPO
            WHERE (PERM.IDSETORPAI IN (".$arr[0].") OR PERM.IDGRUPOPAI IN (SELECT PERM2.IDGRUPO FROM tab_permissoes PERM2 WHERE PERM2.ID IN (".$arr[1]."))) AND PERM.IDGRUPO IS NOT NULL;";

            $stmt = $GLOBALS['conexao']->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetchAll();

            // Se retornar consulta então se faz uma consulta recursiva, retornando até adicionar todos as permissões que este usuário pode gerenciar
            if(count($resultado)>0){

                //Coloca as novas permissões para buscar recursivamente dentro do while
                $arrpermissoes=[];
                foreach($resultado as $permissao){
                    array_push($arrpermissoes,$permissao['ID']);
                }

                $sql = "INSERT INTO temp_permissoes (IDPERMISSAO, NOME, DESCRICAO, IDSETORPAI, IDGRUPO, ORDEM, DIRETOR, INDIVIDUAIS) $sql";

                $stmt = $GLOBALS['conexao']->prepare($sql);
                $stmt->execute($params);
                $resultado = $stmt->fetchAll();
                
            }else{
                $blnsair = true;
            }
        }
        
        if($contadorincluir==0 && $blnincluirpermissaopai==true){

            $where = '';
            $params = [];
            foreach($arrpermissoesinicio as $idperm){
                if($where==''){
                    $where = '?';
                }else{
                    $where .= ', ?';
                }
                array_push($params,$idperm);
            }

            $sql = "INSERT INTO temp_permissoes (IDPERMISSAO, NOME, DESCRICAO, IDSETORPAI, IDGRUPO, ORDEM, DIRETOR, INDIVIDUAIS) SELECT PERM.ID, PERM.NOME, PERM.DESCRICAO, PERM.IDSETORPAI, PERM.IDGRUPO, PERM.ORDEM, PERM.DIRETOR, PERMGRUPO.INDIVIDUAIS
            FROM tab_permissoes PERM
            INNER JOIN tab_permissoesgrupo PERMGRUPO ON PERMGRUPO.ID = PERM.IDGRUPO
            WHERE PERM.ID IN ($where) AND PERM.IDGRUPO IS NOT NULL;";
            $stmt = $GLOBALS['conexao']->prepare($sql);
            $stmt->execute($params);
        }

        $sql = "SELECT TMP.*, PERMG.NOME NOMEGRUPO FROM temp_permissoes TMP
        INNER JOIN tab_permissoesgrupo PERMG ON PERMG.ID = TMP.IDGRUPO
        ORDER BY IDGRUPO, IDSETORPAI, ORDEM;";

        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetchAll();
        
        if($tiporetorno==1){
            return $resultado;
        }elseif($tiporetorno==2){
            $retorno = [];
            foreach($resultado as $permissao){
                array_push($retorno,$permissao['IDPERMISSAO']);
            }
            return $retorno;
        }

    }else{
        return $conexaoStatus;
    }
}

//Busca as permissões que são IDSETORPAI (Permissões que podem atribuir permissões a outros usuários)
function buscaIDsSetorPai(){

    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        // $sql = "SELECT DISTINCT IDSETORPAI FROM tab_permissoes WHERE IDSETORPAI IS NOT NULL";
        $sql = "SELECT DISTINCT PERM.ID FROM tab_permissoes PERM
        WHERE PERM.ID IN (SELECT DISTINCT PERM2.IDSETORPAI FROM tab_permissoes PERM2
        WHERE PERM2.IDSETORPAI IS NOT NULL) 
        UNION 
        SELECT DISTINCT PERM.ID FROM tab_permissoes PERM
        WHERE PERM.IDGRUPO IN (SELECT DISTINCT PERM2.IDGRUPOPAI FROM tab_permissoes PERM2
        WHERE PERM2.IDGRUPOPAI IS NOT NULL) ORDER BY ID;";

        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute();
        //unset($GLOBALS['conexao']);
        
        $resultado = $stmt->fetchAll();
        $retorno = [];
        foreach($resultado as $permissao){
            array_push($retorno,$permissao['ID']);
        }
        return $retorno;
    }else{
        return $conexaoStatus;
    }
}

//Função para retornar parte da matricula
//$tipo 1 = matricula sem o dígito
//$tipo 2 = dígito da matricula
//$tipo 3 = matricula-digito
function midMatricula($matricula, $tipo){
    $digito = substr($matricula,strlen($matricula)-1,1);
    $matricula = substr($matricula,0,strlen($matricula)-1);
    
    if($tipo==1){
        return $matricula;
    }elseif($tipo==2){
        return $digito;
    }elseif($tipo==3){
        return "$matricula-$digito";
    }
}

//Função para baixar foto do servidor para um armazenamento local e após isso poder exibir no sistema a imagem baixada.
//O usuário FTP tem acesso somente a pasta Fotos que está na raiz do 242, se for baixar de outro lugar então se cria outro usuário FTP indicando a pasta que ele irá ter acesso.
//$IDPresoOuRGVisita = nome da foto, pode ser o IDPreso ou o RG da visita, da maneira que é salva a imagem
//$tipo 1 = Fotos de presos
//$tipo 2 = Fotos de visitantes
//$nivelSubpasta = Nivel do include do arquivo que chamou está função. Ex: Voltar uma pasta '../', voltar duas pastas '../../'
function baixarFotoServidor($IDPresoOuCPFVisita,$tipo, $nivelSubpasta=""){
    
    $nomepasta = 'Fotos_sentenciados';
    switch($tipo){
        case 1:
            $nomepasta = 'Fotos_sentenciados';
            break;
        case 2:
            $nomepasta = 'Fotos_visitantes';
            break;
    }

    $servidor = "10.14.239.242";
    $usuario = "ftpuser";
    $senha = "senha";
    $conexaoFTP = ftp_connect($servidor);
    $login = ftp_login($conexaoFTP,$usuario,$senha);
    //Define como passivo, senão da erro de PORTA
    ftp_pasv($conexaoFTP, true);
    //Captura o tamanho do arquivo. Se o arquivo não existir então se retorna -1
    $arquivo = ftp_size($conexaoFTP,"$nomepasta/$IDPresoOuCPFVisita.jpg");
    $retorno = "imagens/sem-foto.png";

    if($arquivo!=-1){
        $origem = $nivelSubpasta."fotos/$nomepasta/$IDPresoOuCPFVisita.jpg";
        $destino = "$nomepasta/$IDPresoOuCPFVisita.jpg";
        /*if(file_exists($destino)){
            rmdir($destino);
        }*/
        ftp_get($conexaoFTP,$origem,$destino,FTP_BINARY,);
        $retorno = "fotos/$nomepasta/$IDPresoOuCPFVisita.jpg";
    }
    
    //Procura dentro da pasta o arquivo. É inviável pois quanto maior a quantidade de arquivos mais demora o resultado
    /*$arquivos=ftp_nlist($conexaoFTP,$nomepasta);
    if(in_array("$nomepasta/$matriculaOuRGVisita.jpg",$arquivos)){
    }else{
        echo 'Não encontrado';
    }*/

    ftp_close($conexaoFTP);
    return $retorno;
}

//Retorna um array some com os nomes dos arquivos, retirando o caminho
function clean_scandir($dir){
    return array_values(array_diff(scandir($dir),array('..','.')));
}

//Imprime o array com a tag <pre>
function pre_r($array){
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

function removeEspacoDuplo($texto){
    $texto = str_replace('  ',' ',$texto);
    return $texto;
}

function removeTodoEspaco($texto){
    $texto = str_replace(' ','',$texto);
    return $texto;
}

//Retorna um array de palavras separadas por espacamento simples
function retornaArrayPalavras($texto){
    $texto = removeEspacoDuplo($texto);
    return explode(' ', $texto);
}

//Busca dados do Diretor (Cargo, sigla e nome) com base na data do documento informada
function buscaDadosDiretor($idpermissao,$data,$idturno=5){
    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        $params=[];
        array_push($params,$data);
        array_push($params,$data);
        array_push($params,$data);
        array_push($params,$idpermissao);
        
        $sql = "SELECT USPER.ID IDDIRETOR, USPER.TEMPORARIO, USPER.IDUSUARIO, US.NOME, USPER.SUBSTITUTO, PERM.NOME SIGLA, PERM.NOMECOMPLETO CARGO, USPER.IDPERMISSAO
        FROM tab_usuariospermissoes USPER
        INNER JOIN tab_usuarios US ON US.ID = USPER.IDUSUARIO
        INNER JOIN tab_permissoes PERM ON PERM.ID = USPER.IDPERMISSAO
        WHERE USPER.DATAINICIO <= ? AND (USPER.DATATERMINO IS NULL OR USPER.DATATERMINO >= ?) AND (USPER.DATAEXCLUSOREGISTRO >= ? OR USPER.DATAEXCLUSOREGISTRO IS NULL) AND USPER.IDPERMISSAO IN (?)
        ORDER BY USPER.TEMPORARIO DESC;";
    
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute($params);
        //unset($GLOBALS['conexao']);
        $resultado = $stmt->fetchAll();
        
        if(count($resultado)){
            // $substituto = '';
            // if($resultado[0]['SUBSTITUTO']==1){
            //     $substituto = ' - Substituto';
            // }
            // $retorno = array('IDUSUARIO' => $resultado[0]['IDUSUARIO'], 'CARGO' => $resultado[0]['NOMEPERMISSAOCOMPLETO'] . $substituto, 'NOME' => buscaDadosLogPorPeriodo($resultado[0]['IDUSUARIO'],'NOME',1,$data), 'SIGLA' => $resultado[0]['NOMEPERMISSAO']);

            return $resultado;
        }else{
            return 'Diretor não encontrado';
        }
    }else{
        echo $conexaoStatus;
        return false;
        exit();
    }
}

//Busca dados da permissão cadastrada 
function buscaDadosIDPermissao($idpermissao){
    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        $params=[];
        array_push($params,$idpermissao);
        
        $sql = "SELECT PERM.NOME NOMEPERMISSAO, PERM.NOMECOMPLETO NOMECOMPLETOPERMISSAO, US.NOME NOMEDIRETOR, USPERM.SUBSTITUTO
        FROM tab_usuariospermissoes USPERM
        INNER JOIN tab_usuarios US ON US.ID = USPERM.IDUSUARIO
        INNER JOIN tab_permissoes PERM ON PERM.ID = USPERM.IDPERMISSAO
        WHERE USPERM.ID = ?;";
    
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute($params);
        //unset($GLOBALS['conexao']);
        $resultado = $stmt->fetchAll();
        
        if(count($resultado)){
            return $resultado;
        }else{
            return [];
        }
    }else{
        echo $conexaoStatus;
        return [];
        exit();
    }
}

//Busca dados de Raio e Cela do Preso (Raio, cela e status de cela seguro) com base na data do pesquisa informada
//$datahora = Informar data e hora específica para a busca
//$tipoespecial = ID do tipo da cela especial a ser retornada
function buscaDadosRaioCelaPreso($idpreso,$datahora,$tipoespecial=0){      
    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        $params = array($idpreso,$datahora,$datahora,$idpreso,$datahora);
        $sql = "SELECT ID, RAIO, CELA, 1 ORDEM FROM cadastros_mudancacela WHERE IDPRESO = ? AND DATACADASTRO < ? AND DATAATUALIZACAO >= ?
        UNION
        SELECT ID, RAIO, CELA, 2 ORDEM FROM cadastros_mudancacela WHERE IDPRESO = ? AND DATACADASTRO < ? AND DATAATUALIZACAO IS NULL
        ORDER BY ORDEM, ID DESC;";
    
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute($params);
        //unset($GLOBALS['conexao']);
        $resultado = $stmt->fetchAll();

        if(count($resultado)){
            $retorno = array(
                'RAIO' => buscaDadosLogPorPeriodo($resultado[0]['RAIO'],'NOME',4,$datahora), 'CELA' => $resultado[0]['CELA'],
                'ESPECIAL' => buscaCelaExcessao($tipoespecial,$datahora,$resultado[0]['RAIO'],$resultado[0]['CELA']));
            return $retorno;
        }else{
            return false;
        }
    }else{
        echo $conexaoStatus;
        return false;
        exit();
    }
}

//Busca dados informações se a cela foi ou ainda é cela de excessão na data informada
function buscaCelaExcessao($tipo,$data,$raio,$cela){
    if($tipo==0){
        return false;
    }
    if(strlen($data)==10){
        $data = "$data 23:59:59";
    }

    $conexaoStatus = conectarBD();
    if($conexaoStatus===true){
        $sql = "SELECT *, 1 ORDEM FROM tab_raioscelasexcecoes WHERE IDRAIO = :raio AND CELA = :cela AND IDTIPO = :tipo AND DATAINICIO <= :datahora AND DATATERMINO >= :datahora AND IDEXCLUSOREGISTRO IS NULL
        UNION
        SELECT *, 2 ORDEM FROM tab_raioscelasexcecoes WHERE IDRAIO = :raio AND CELA = :cela AND IDTIPO = :tipo AND DATAINICIO <= :datahora AND DATATERMINO IS NULL AND IDEXCLUSOREGISTRO IS NULL ORDER BY ORDEM, ID DESC;";
    
        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->bindParam('tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindParam('raio', $raio, PDO::PARAM_STR);
        $stmt->bindParam('cela', $cela, PDO::PARAM_STR);
        $stmt->bindParam('datahora', $data, PDO::PARAM_STR);
        $stmt->execute();
        //unset($GLOBALS['conexao']);
        $resultado = $stmt->fetchAll();
        
        if(count($resultado)){
            return 1;
        }else{
            return 0;
        }
    }else{
        echo $conexaoStatus;
        return false;
        exit();
    }
}

//Busca informações baseada na data que se é buscado.
//Se informar a data sem horarío, então se busca a última alteração do dia, caso contrário, busca a última alteracão até o horário informado.
function buscaDadosLogPorPeriodo($id,$campo,$tabela,$data){
    $conexaoStatus = conectarBD();
    
    if(strlen($data)==10){
        $data = "$data 23:59:59";
    }
    $excessaoID = 'ID';

    switch($tabela){
        case 1: $tabela = 'tab_usuarios'; break;
        case 2: $tabela = 'tab_diretores'; break;
        case 3: $tabela = 'cadastros'; $excessaoID = 'MATRICULA'; break;
        case 4: $tabela = 'tab_raioscelas'; break;
        case 5: $tabela = 'entradas_presos'; break;
    }
    $tab_log = $tabela . '_log';

    if($conexaoStatus===true){
        $sql = "SELECT CASE WHEN (SELECT TAB_LOG.NOVO FROM $tab_log TAB_LOG 
        WHERE TAB_LOG.CAMPOATUALIZADO = '$campo' AND TAB_LOG.IDREFERENCIA = $id 
        AND TAB_LOG.DATAATUALIZACAO <= ? ORDER BY TAB_LOG.ID DESC LIMIT 1) IS NOT NULL THEN (SELECT TAB_LOG.NOVO FROM $tab_log TAB_LOG 
        WHERE TAB_LOG.CAMPOATUALIZADO = '$campo' AND TAB_LOG.IDREFERENCIA = $id 
        AND TAB_LOG.DATAATUALIZACAO <= ? ORDER BY TAB_LOG.ID DESC LIMIT 1)
        WHEN (SELECT TAB_LOG.ANTIGO FROM $tab_log TAB_LOG 
        WHERE TAB_LOG.CAMPOATUALIZADO = '$campo' AND TAB_LOG.IDREFERENCIA = $id 
        AND TAB_LOG.DATAATUALIZACAO >= ? ORDER BY TAB_LOG.ID ASC LIMIT 1) IS NOT NULL THEN (SELECT TAB_LOG.ANTIGO FROM $tab_log TAB_LOG 
        WHERE TAB_LOG.CAMPOATUALIZADO = '$campo' AND TAB_LOG.IDREFERENCIA = $id 
        AND TAB_LOG.DATAATUALIZACAO >= ? ORDER BY TAB_LOG.ID ASC LIMIT 1) ELSE (SELECT $campo FROM $tabela WHERE $excessaoID = $id) END $campo;";
    
        //echo json_encode(array('MENSAGEM' => "<li class = 'mensagem-aviso'> Linha ".__LINE__.", sql = $sql </li>"));exit();
        $params = [$data,$data,$data,$data];

        $stmt = $GLOBALS['conexao']->prepare($sql);
        $stmt->execute($params);
        //unset($GLOBALS['conexao']);
        
        $resultado = $stmt->fetchAll();
        if(count($resultado)){
            return $resultado[0][$campo];
        }else{
            return "Dados de $campo não encontrado";
        }
    }else{
        echo $conexaoStatus;
        return false;
        exit();
    }
}

function retornaNomeTabela($tabela){
    switch($tabela){
        case 1:
            $tabela = "entradas_presos";
            break;

        case 2:
            $tabela = "chefia_mudancacela";
            break;

        case 3:
            $tabela = "cimic_transferencias";
            break;

        case 4:
            $tabela = "cimic_apresentacoes";
            break;

        case 5:
            $tabela = "cimic_apresentacoes_internas_presos";
            break;

        case 6:
            $tabela = "enf_atendimentos";
            break;

        case 7:
            $tabela = "chefia_atendimentos";
            break;

        case 8:
            $tabela = "cimic_exclusoes";
            break;

        case 9:
            $tabela = "rol_visitantes_presos";
            break;

        default:
            $tabela = 0;
            break;
    }
    return $tabela;
}