<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['name'];

    // القيم التابعة لهذه الخاصية (مثل: أحمر، أسود التابعة لخاصية اللون)
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}