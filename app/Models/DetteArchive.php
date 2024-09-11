<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DetteArchive extends Model
{
   protected $connection = 'mongodb';
   protected $fillable = ['client_id', 'nom','telephone','dettes'];

   public function setCollect($collect){
    $this->collection = $collect;
   }
}
