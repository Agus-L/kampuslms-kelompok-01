<?php

namespace App\Http\Resources\V1;

class AssignmentCollection extends PaginatedResourceCollection
{
    public $collects = AssignmentResource::class;
}
