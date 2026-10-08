<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SdgCategory extends Model
{
    public function label()
    {
        return $this->name;
    }
}
