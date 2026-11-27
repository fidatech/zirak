<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Review = 'review'; // sent by a teacher, waiting for admin approval
    case Published = 'published';
    case Archived = 'archived';
}
