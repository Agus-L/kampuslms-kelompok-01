<?php

namespace App\Http\Resources\V1;

class NotificationCollection extends PaginatedResourceCollection
{
    public $collects = NotificationResource::class;
}
