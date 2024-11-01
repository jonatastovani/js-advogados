import { commonFunctions } from "../commons/commonFunctions";

export class funcoesPresos {

    static retornaDigitoMatricula(matricula) {
        matricula = commonFunctions.returnsOnlyNumber(matricula);

        let mult = 2;
        let soma = 0;
        let s = "";

        for (let i = matricula.length - 1; i >= 0; i--) {
            s = (mult * parseInt(matricula[i], 10)) + s;
            if (--mult < 1) {
                mult = 2;
            }
        }

        for (let i = 0; i < s.length; i++) {
            soma = soma + parseInt(s[i], 10);
        }

        soma = soma % 10;

        if (soma !== 0) {
            soma = 10 - soma;
        }

        return parseInt(soma, 10);
    }

    static retornaMatriculaFormatada(matricula, tipo = 1, inserirPontuacao = true) {
        // Verifica se o tipo é válido (1, 2 ou 3)
        if (tipo < 1 || tipo > 3) {
            throw new Error('O tipo deve ser 1, 2 ou 3.');
        }
    
        // Remove qualquer pontuação existente na matrícula
        matricula = matricula.replace(/[^\d]/g, '');
    
        // Verifica se a matrícula tem pelo menos um número
        if (matricula.length === 0) {
            throw new Error('A matrícula deve conter pelo menos um número.');
        }
    
        // Converte para string e obtém o último dígito
        const digito = matricula.slice(-1);
        matricula = matricula.slice(0, -1);
    
        // Insere pontuação no milhar, se necessário
        if (inserirPontuacao) {
            matricula = Number(matricula).toLocaleString('pt-BR', { useGrouping: true });
        }
    
        // Formata a matrícula conforme o tipo
        let matriculaFormatada;
        switch (tipo) {
            case 1:
                matriculaFormatada = matricula + '-' + digito;
                break;
    
            case 2:
                matriculaFormatada = matricula;
                break;
    
            case 3:
                matriculaFormatada = digito;
                break;
    
            default:
                throw new Error('Tipo de formato inválido.');
        }
    
        return matriculaFormatada;
    }

}