<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramType extends Model
{
    public function label()
    {
        return $this->name;
    }
}
