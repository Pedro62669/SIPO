<?php

namespace App\Enums;

enum ParametrizacaoClassificacao: string
{
    case Geral = 'geral';
    case Custeio = 'custeio';
    case Pessoal = 'pessoal';
    case Investimento = 'investimento';
    case Terceirizacao = 'terceirizacao';
}
