<?php

namespace App\Enums;

enum CertificateType: string
{
    case Achievement = 'achievement';
    case Completion = 'completion';
    case None = 'none';

    public function label(): string
    {
        return __('app.certificate_type.'.$this->value);
    }
}
