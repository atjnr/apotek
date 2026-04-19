<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resep;
use App\Models\DetailResep;
use App\Models\Obat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ResepController extends Controller
{
    public function index()
    {
        $reseps = Resep::with('obats')->where('id_dokter', Auth::guard('pengguna')->id())->get();
        return view('dokter.resep.index', compact('reseps'));
    }

    public function create()
    {
        $obat = Obat::all();
        return view('dokter.resep.create', compact('obat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pasien' => 'required|string|max:255',
            'tanggal_resep' => 'required|date',
            'obat_id' => 'required|array|min:1',
            'obat_id.*' => 'required|exists:obat,id_obat',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|numeric|min:1',
        ]);

        try {
            $resep = Resep::create([
                'nama_pasien' => $request->nama_pasien,
                'id_dokter' => Auth::guard('pengguna')->id(),
                'tanggal_resep' => $request->tanggal_resep,
                'keterangan' => $request->keterangan,
            ]);

            foreach ($request->obat_id as $i => $id_obat) {
                DetailResep::create([
                    'id_resep' => $resep->id_resep,
                    'id_obat' => $id_obat,
                    'jumlah' => $request->jumlah[$i],
                    'keterangan' => $request->keterangan_obat[$i] ?? null,
                ]);
            }

            return redirect()->route('resep.index')->with('success', 'Resep berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->withErrors('Gagal simpan: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        $resep = Resep::with(['detail.obat'])->findOrFail($id);
        return view('dokter.resep.show', compact('resep'));
    }


    public function destroy($id)
    {
        $resep = Resep::findOrFail($id);
        $resep->detail()->delete();
        $resep->delete();

        return redirect()->route('dokter.resep.index')->with('success', 'Resep berhasil dihapus.');
    }
}
