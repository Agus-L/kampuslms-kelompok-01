<?php

namespace App\Http\Resources\V1;

class SubmissionCollection extends PaginatedResourceCollection
{
    public $collects = SubmissionResource::class;
}
