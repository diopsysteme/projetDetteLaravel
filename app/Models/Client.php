<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'surnom',
        'telephone',
        'address',
        'qrcode'
    ];

    // Attributs non mass-assignables
    // protected $guarded = [
    //     'user_id',
    // ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function dettes()
    {
        return $this->hasMany(Dette::class);
    }
    public function scopeFilterByRequest($query, $request)
    {
        if ($request->has('compte')) {
            $comptes = $request->input('compte');
            if ($comptes === 'oui') {
                $query->has('user');
            } elseif ($comptes === 'non') {
                $query->doesntHave('user');
            }
        }
        if ($request->has('active')) {
            $active = $request->input('active') === 'oui' ? true : false;
            $query->whereHas('user', function ($q) use ($active) {
                $q->where('active', $active);
            });
        }
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        return $query;
    }

    protected static function booted()
    {
        static::addGlobalScope('byPhone', function (Builder $builder) {
            if (request()->has('telephon')) {
                $telephone = request()->input('telephone');
                $builder->where('telephone', 'like', '%' . $telephone . '%');
            }
        });
    }
    public function scopeWithoutPhoneFilter($query)
    {
        return $query->withoutGlobalScope('byPhone');
    }

    
}
