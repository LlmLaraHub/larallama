<?php

namespace App\Domains\Transformers;

enum TypeEnum: string
{
    case GenericTransformer = 'generic_transformer';
    case CrmTransformer = 'crm_transformer';

    case EmailTransformer = 'email_transformer';
}
