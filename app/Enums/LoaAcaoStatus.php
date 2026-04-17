<?php

namespace App\Enums;

enum LoaAcaoStatus: string
{
    case Ativa = 'ativa';
    case Excluida = 'excluida';
    case Nova = 'nova';
    case Editada = 'editada';
}
