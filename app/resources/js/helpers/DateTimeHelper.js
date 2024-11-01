export class DateTimeHelper {
    // /**
    //  * Converte uma data do formato brasileiro (DD-MM-YYYY) para o formato internacional (YYYY-MM-DD).
    //  * @param {string} data - Data no formato brasileiro.
    //  * @return {string} - Data convertida no formato internacional.
    //  */
    // static converteDataHoraBrasileira(data) {
    //     return moment(data, "DD-MM-YYYY HH:mm:ss").format("YYYY-MM-DD HH:mm:ss");
    // }

    /**
     * Retorna a diferença entre duas datas.
     * @param {string} DataMenor - Data inicial no formato YYYY-MM-DD HH:mm:ss.
     * @param {string} DataMaior - Data final no formato YYYY-MM-DD HH:mm:ss.
     * @param {number} tipo - Tipo de diferença a ser retornada (1 = Dias, 3 = Anos, 4 = Horas, 5 = Minutos, 6 = Segundos).
     * @return {number} - Diferença entre as duas datas no tipo especificado.
     */
    static retornaDiferencaDeDataEHora(DataMenor, DataMaior, tipo) {
        const ms = moment(DataMaior, "YYYY-MM-DD HH:mm:ss").diff(moment(DataMenor, "YYYY-MM-DD HH:mm:ss"));
        const duration = moment.duration(ms);

        switch (tipo) {
            case 1: return duration.asDays();
            case 3: return duration.asYears();
            case 4: return duration.asHours();
            case 5: return duration.asMinutes();
            case 6: return duration.asSeconds();
            default: return 0;
        }
    }

    /**
     * Adiciona dias, meses, anos, horas, minutos ou segundos a uma data.
     * @param {Date} data - Data inicial.
     * @param {number} quantidade - Quantidade de tempo a ser adicionada.
     * @param {number} tipo - Tipo de dado a ser adicionado (1 = Dias, 2 = Meses, 3 = Anos, 4 = Horas, 5 = Minutos, 6 = Segundos).
     * @return {Date} - Data com o tempo adicionado.
     */
    static retornaSomaDataEHora(data, quantidade, tipo) {
        const novaData = new Date(data);

        switch (tipo) {
            case 1: novaData.setDate(novaData.getDate() + quantidade); break;
            case 2: novaData.setMonth(novaData.getMonth() + quantidade); break;
            case 3: novaData.setFullYear(novaData.getFullYear() + quantidade); break;
            case 4: novaData.setHours(novaData.getHours() + quantidade); break;
            case 5: novaData.setMinutes(novaData.getMinutes() + quantidade); break;
            case 6: novaData.setSeconds(novaData.getSeconds() + quantidade); break;
        }

        return novaData;
    }

    /**
     * Retorna um formato de data ou hora específico.
     * @param {string} DataHora - Data e hora.
     * @param {number} tipo - Tipo de dado a ser retornado (1 a 12 conforme solicitado).
     * @param {boolean} converter - Se true, converte de padrão brasileiro para internacional.
     * @return {string} - Data ou hora no formato especificado.
     */
    static retornaDadosDataHora(DataHora, tipo, converter = false) {
        if (converter) {
            DataHora = this.converteDataHoraBrasileira(DataHora);
        }

        if (DataHora.length === 10) {
            DataHora += ' 00:00:00';
        }

        const data = new Date(DataHora);
        const dd = String(data.getDate()).padStart(2, '0');
        const mm = String(data.getMonth() + 1).padStart(2, '0');
        const yyyy = data.getFullYear();
        const H = String(data.getHours()).padStart(2, '0');
        const i = String(data.getMinutes()).padStart(2, '0');
        const s = String(data.getSeconds()).padStart(2, '0');

        switch (tipo) {
            case 1: return `${yyyy}-${mm}-${dd}`; // YYYY-mm-dd
            case 2: return `${dd}/${mm}/${yyyy}`; // dd/mm/YYYY
            case 3: return dd; // Dia
            case 4: return mm; // Mês
            case 5: return yyyy; // Ano
            case 6: return `${H}:${i}`; // H:i
            case 7: return `${H}:${i}:${s}`; // H:i:s
            case 8: return H; // Horas
            case 9: return i; // Minutos
            case 10: return s; // Segundos
            case 11: return `${yyyy}-${mm}-${dd} ${H}:${i}:${s}`; // YYYY-mm-dd H:i:s
            case 12: return `${dd}/${mm}/${yyyy} ${H}:${i}`; // dd/mm/YYYY H:i
            default: return '';
        }
    }

    /**
     * Verifica se uma data é inferior a uma determinada quantidade de horas da data atual.
     * @param {string} DataHora - A data de criação no formato YYYY-MM-DD HH:mm:ss.
     * @param {number} horasLimite - O limite de horas a ser comparado.
     * @return {boolean} - Retorna true se for inferior ao limite de horas, false caso contrário.
     */
    static ehMenosDeXHoras(DataHora, horasLimite) {
        const msDiff = moment().diff(moment(DataHora, "YYYY-MM-DD HH:mm:ss"));
        const diffInHours = moment.duration(msDiff).asHours();
        return diffInHours < horasLimite;
    }
}
