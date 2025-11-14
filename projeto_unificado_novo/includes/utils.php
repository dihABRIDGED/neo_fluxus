<?php

/**
 * Arquivo de funções utilitárias para o sistema.
 */

/**
 * Gera uma senha genérica baseada no nome e sobrenome do usuário.
 * Padrão: 2 primeiras letras do primeiro nome + 2 primeiras letras do último sobrenome + 4 números aleatórios.
 * @param string $nomeCompleto Nome completo do usuário.
 * @return string Senha genérica gerada.
 */
function gerarSenhaGenerica(string $nomeCompleto): string
{
    // 1. Limpar e normalizar o nome
    $nomeCompleto = trim(preg_replace('/\s+/', ' ', $nomeCompleto));
    $partesNome = explode(' ', $nomeCompleto);

    // 2. Obter o primeiro nome
    $primeiroNome = $partesNome[0] ?? '';
    $primeiroNome = preg_replace('/[^a-zA-Z]/', '', $primeiroNome); // Remover caracteres não alfabéticos
    $primeiroNome = strtolower($primeiroNome);

    // 3. Obter o último sobrenome
    $ultimoSobrenome = end($partesNome);
    if (count($partesNome) > 1 && $ultimoSobrenome === $primeiroNome) {
        // Caso o nome seja composto por apenas um nome (ex: "Ana"), o último sobrenome será o próprio nome.
        // Neste caso, vamos considerar o último nome como o primeiro nome.
        $ultimoSobrenome = '';
    } elseif (count($partesNome) > 1) {
        // Se houver mais de uma parte, garantir que o último sobrenome não seja uma preposição comum
        $preposicoes = ['de', 'da', 'do', 'dos', 'das', 'e'];
        while (in_array(strtolower($ultimoSobrenome), $preposicoes) && count($partesNome) > 1) {
            array_pop($partesNome);
            $ultimoSobrenome = end($partesNome);
        }
    }
    
    $ultimoSobrenome = preg_replace('/[^a-zA-Z]/', '', $ultimoSobrenome);
    $ultimoSobrenome = strtolower($ultimoSobrenome);

    // 4. Extrair as 2 primeiras letras de cada parte
    $parteNome = substr($primeiroNome, 0, 2);
    $parteSobrenome = substr($ultimoSobrenome, 0, 2);

    // 5. Preencher se tiver menos de 2 caracteres (Regra de Robustez)
    while (strlen($parteNome) < 2) {
        $parteNome .= 'x'; // Caractere de preenchimento
    }
    while (strlen($parteSobrenome) < 2) {
        $parteSobrenome .= 'x'; // Caractere de preenchimento
    }

    // 6. Gerar 4 números aleatórios
    $numerosAleatorios = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

    // 7. Combinar para formar a senha
    $senhaGenerica = $parteNome . $parteSobrenome . $numerosAleatorios;

    return $senhaGenerica;
}


/**
 * Gera um número de matrícula único para o aluno.
 * Padrão: Ano atual (2 dígitos) + Mês atual (2 dígitos) + 6 dígitos aleatórios.
 * @return string Matrícula gerada.
 */
function gerarMatricula(): string
{
    $ano = date('y');
    $mes = date('m');
    $aleatorio = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    return $ano . $mes . $aleatorio;
}

?>
