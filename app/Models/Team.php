<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name', 'description'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'team', 'name');
    }
}
