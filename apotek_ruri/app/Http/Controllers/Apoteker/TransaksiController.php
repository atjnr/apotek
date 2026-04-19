<?php

namespace App\Http\Controllers\Apoteker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Obat;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::with(['obat', 'pengguna'])->orderByDesc('tanggal_transaksi')->get();
        return view('apoteker.transaksi.index', compact('transaksi'));
    }

    public function create()
    {
        $obat = Obat::all();
        return view('apoteker.transaksi.create', compact('obat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_obat' => 'required|exists:obat,id_obat',
            'jenis' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'tanggal_transaksi' => 'required|date',
            'tanggal_kadaluarsa' => 'nullable|date', // hanya diperlukan saat masuk
        ]);

        $obat = Obat::findOrFail($request->id_obat);
        $jumlah = $request->jumlah;
        $total = $jumlah * $obat->harga;

        if ($request->jenis == 'keluar') {
            if ($obat->stok < $jumlah) {
                return redirect()->back()->withErrors(['msg' => 'Stok tidak mencukupi']);
            }
            $obat->stok -= $jumlah;
            $obat->tanggal_keluar = $request->tanggal_transaksi;
        } else {
            $obat->stok += $jumlah;
            $obat->tanggal_masuk = $request->tanggal_transaksi;

            if ($request->tanggal_kadaluarsa && !$obat->tanggal_kadaluarsa) {
                $obat->tanggal_kadaluarsa = $request->tanggal_kadaluarsa;
            }
        }

        $obat->save();

        Transaksi::create([
            'id_obat' => $request->id_obat,
            'id_pengguna' => Auth::guard('pengguna')->id(),
            'jenis' => $request->jenis,
            'jumlah' => $jumlah,
            'total' => $total,
            'tanggal_transaksi' => $request->tanggal_transaksi
        ]);

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil disimpan');
    }

    public function ajaxSearch(Request $request)
    {
        $query = Transaksi::with('obat');

        if ($request->nama) {
            $query->whereHas('obat', function ($q) use ($request) {
                $q->where('nama_obat', 'like', '%' . $request->nama . '%');
            });
        }

        if ($request->jenis) {
            $query->where('jenis_transaksi', $request->jenis);
        }

        if ($request->dari && $request->sampai) {
            $query->whereBetween('tanggal_transaksi', [$request->dari, $request->sampai]);
        } elseif ($request->dari) {
            $query->whereDate('tanggal_transaksi', '>=', $request->dari);
        } elseif ($request->sampai) {
            $query->whereDate('tanggal_transaksi', '<=', $request->sampai);
        }

        $data = $query->orderBy('tanggal_transaksi', 'asc')->get();

        $result = $data->map(function ($item) {
            return [
                'nama_obat' => $item->obat->nama_obat ?? '-',
                'jenis_transaksi' => ucfirst($item->jenis_transaksi),
                'jumlah' => $item->jumlah,
                'total_harga' => $item->total_harga,
                'tanggal_transaksi' => $item->tanggal_transaksi,
            ];
        });

        return response()->json($result);
    }

}
