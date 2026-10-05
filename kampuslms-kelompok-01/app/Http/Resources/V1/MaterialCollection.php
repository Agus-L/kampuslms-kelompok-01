<?php

namespace App\Http\Resources\V1;

class MaterialCollection extends PaginatedResourceCollection
{
    public $collects = MaterialResource::class;
}
