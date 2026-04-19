<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailResep extends Model
{
    protected $table = 'detail_resep';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['id_resep', 'id_obat', 'jumlah'];

    public function obat()
    {
        return $this->belongsTo(Obat::class, 'id_obat');
    }
}
