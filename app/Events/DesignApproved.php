<?php

namespace App\Events;

use App\Models\DesignRequest;
use App\Models\DesignVersion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DesignApproved
{
    use Dispatchable, SerializesModels;
    public function __construct(public DesignRequest $design, public DesignVersion $version) {}
}
