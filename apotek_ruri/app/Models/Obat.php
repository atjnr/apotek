<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    use HasFactory;

    protected $table = "obat";
    protected $primaryKey = 'id_obat';

    protected $fillable = [
        "nama_obat",
        "stok",
        "harga",
        "tanggal_kadaluarsa",
        "tanggal_masuk",
        "tanggal_keluar",
        "status",
    ];

    protected $dates =[
        "tanggal_kadaluarsa", 
        "tanggal_masuk", 
        "tanggal_keluar"
    ];

    public function scopeFefo($query)
    {
        return $query->where('stok', '>', 0)
                     ->orderBy('tanggal_kadaluarsa', 'asc');
    }
    // public static function fefo()
    // {
    //     return self::where('stok', '>', 0)
    //     ->orderBy("tanggal_kadaluarsa", "asc")
    //     ->get();
    // }
}

