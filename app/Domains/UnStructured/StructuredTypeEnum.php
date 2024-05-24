<?php

namespace App\Domains\UnStructured;

enum StructuredTypeEnum: string
{
    case Title = 'title';
    case TableRow = 'table_row';
    case Image = 'image';
    case Footer = 'footer';
    case Narrative = 'narrative';
    case HeaderOne = 'header_one';
    case HeaderTwo = 'header_two';
    case HeaderThree = 'header_three';
    case EmailTo = 'email_to';
    case EmailFrom = 'email_from';
    case EmailBody = 'email_body';
    case EmailSubject = 'email_subject';
    case EmailHeader = 'email_header';
    case Phone = 'phone';
    case PersonName = 'name';
    case Social = 'social';
    case Table = 'table';
    case Raw = 'raw';

}
