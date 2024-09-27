<?php

namespace App\Enums;

class TipoFotoEnum
{
    const PRESO = 'P';
    const ADVOGADO = 'A';
    const VISITANTEPRESO = 'VP';
    const VISITANTEUNIDADE = 'VU';
    const RELIGIOSO = 'R';
    const FUNCIONARIO = 'F';

    // Outros tipos, se houver
}

class SubTipoFotoEnum
{
    const FRONTAL = 'FR';
    const PERFILESQUERDO = 'PE';
    const PERFILDIREITO = 'PD';
    const SINAIS = 'SI';

    // Outros subtipos, se houver
}
