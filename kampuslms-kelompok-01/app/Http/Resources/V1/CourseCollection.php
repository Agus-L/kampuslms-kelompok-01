<?php

namespace App\Http\Resources\V1;

class CourseCollection extends PaginatedResourceCollection
{
    public $collects = CourseResource::class;
}
