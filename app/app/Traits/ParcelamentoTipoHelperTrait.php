<?php

namespace App\Traits;

trait ParcelamentoTipoHelperTrait
{
    /**
     * Ajusta a data de vencimento com base no dia de vencimento informado.
     * Se o dia de vencimento não existir no mês seguinte, ajusta para o último dia do mês.
     * Se o próximo mês já tiver um pagamento (caso fevereiro seja ignorado), permite que haja duas parcelas em março.
     *
     * @param \DateTime $dataVencimento A data de vencimento que será ajustada.
     * @param int $diaVencimento O dia de vencimento informado.
     * @return \DateTime A data ajustada para o próximo mês com o dia correto ou o último dia do mês.
     */
    static public function ajustarDataVencimentoSeguinte(\DateTime $dataVencimento, int $diaVencimento): \DateTime
    {
        // Clona a data original para evitar modificações no objeto original
        $dataClone = clone $dataVencimento;

        // Calcula o mês seguinte de forma manual
        $mesSeguinte = (int) $dataVencimento->format('m') + 1;
        $ano = (int) $dataVencimento->format('Y');

        // Se ultrapassar dezembro, ajusta para janeiro do próximo ano
        if ($mesSeguinte > 12) {
            $mesSeguinte = 1;
            $ano += 1;
        }

        // Tenta definir a nova data com o dia de vencimento informado
        $dataClone->setDate($ano, $mesSeguinte, $diaVencimento);

        // Verifica se o dia ajustado é o correto, caso contrário, ajusta para o último dia do mês
        if ($dataClone->format('d') != $diaVencimento) {
            // Ajusta para o último dia do mês se o dia informado não existir (ex: 30 de fevereiro)
            $dataClone->setDate($ano, $mesSeguinte, 1); // Primeiro dia do mês
            $dataClone->modify('last day of this month'); // Modifica para o último dia do mês
        }

        return $dataClone;
    }

    /**
     * Ajusta a data da primeira parcela com base no mês de início (Y-m) e no dia de vencimento.
     * Se o dia informado não existir no mês (ex: 31 de fevereiro), ajusta para o último dia do mês.
     *
     * @param string $mesAnoInicio Mês e ano no formato "Y-m", ex: "2025-02"
     * @param int $diaVencimento Dia de vencimento desejado
     * @return \DateTime Data ajustada corretamente
     */
    public static function ajustarDataVencimentoPrimeiraParcela(string $mesAnoInicio, int $diaVencimento): \DateTime
    {
        [$ano, $mes] = explode('-', $mesAnoInicio);

        // Tenta criar a data com o dia desejado
        $data = \DateTime::createFromFormat('Y-m-d', "{$ano}-{$mes}-{$diaVencimento}");

        // Se a data resultante tiver um mês diferente, quer dizer que o dia era inválido (ex: 31/02 virou 03/02)
        if ((int)$data->format('m') !== (int)$mes) {
            $data = new \DateTime("{$ano}-{$mes}-01");
            $data->modify('last day of this month');
        }

        return $data;
    }
}
