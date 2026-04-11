<?php

namespace Nawar16\CacheWrapper\tests\Models;

use Illuminate\Database\Eloquent\Model;

class TestUser extends Model {
    protected $table = 'users';
    protected $guarded = [];
}