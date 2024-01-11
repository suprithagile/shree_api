<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'id',
        'is_header',
        'title',
        'icon',
        'href',
        'parent_id',
        'seq',
        'updated_at',
        'created_at',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['parent_name'];

    public function child()
    {
        return $this->hasMany('App\Models\Menu', 'parent_id', 'id')->orderBy('seq', 'asc')->with('child');
    }

    public function children()
    {
        return $this->hasMany('App\Models\Menu', 'parent_id', 'id')->select(array('id', 'title as name', 'parent_id'))->orderBy('seq', 'asc')->with('children');
    }

    public function getParentNameAttribute()
    {
        return Menu::where('id', $this->parent_id)->pluck('title')->first();
    }

}
