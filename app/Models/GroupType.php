<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupType extends Model
{
    public function label()
    {
        return $this->name;
    }
}
