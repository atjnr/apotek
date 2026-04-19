<?php
namespace App\Http\Controllers\Apoteker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Obat;
use Carbon\Carbon;


class ObatController extends Controller
{
    public function index()
    {
        $obat = Obat::orderBy('tanggal_kadaluarsa')->get()->map(function ($item) {
            $item->status_stok = $item->stok == 0
                ? 'Habis'
                : ($item->stok <= 10 ? 'Stok Menipis' : 'Tersedia');

            $exp = Carbon::parse($item->tanggal_kadaluarsa);
            $now = Carbon::now();
            $selisih = $exp->diffInDays($now, false);

            $item->status_kadaluarsa = $selisih < 0
                ? 'Belum Kadaluarsa'
                : ($selisih <= 30 ? 'Hampir Kadaluarsa' : 'Kadaluarsa');

            return $item;
        });

        return view('apoteker.obat.index', compact('obat'));
    }

    public function create()
    {
        return view('apoteker.obat.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_obat' => 'required|string',
            'stok' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'tanggal_masuk' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after_or_equal:tanggal_masuk',
            'tanggal_keluar' => 'nullable|date',
        ]);

        Obat::create([
            'nama_obat' => $request->nama_obat,
            'stok' => $request->stok,
            'harga' => $request->harga,
            'tanggal_masuk' => $request->tanggal_masuk,
            'tanggal_keluar' => $request->tanggal_keluar ?? null,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
        ]);

        return redirect()->route('obat.index')->with('success', 'Obat berhasil ditambahkan');
    }

    public function edit($id_obat)
    {
        $obat = Obat::findOrFail($id_obat);
        return view('apoteker.obat.edit', compact('obat'));
    }

    public function update(Request $request, $id_obat)
    {
        $request->validate([
            'nama_obat' => 'required|string',
            'stok' => 'required|integer|min:0',
            'harga' => 'required|numeric|min:0',
            'tanggal_masuk' => 'required|date',
            'tanggal_kadaluarsa' => 'required|date|after_or_equal:tanggal_masuk',
            'tanggal_keluar' => 'nullable|date',
        ]);

        $obat = Obat::findOrFail($id_obat);

        // Jika tanggal_kadaluarsa berubah, buat data baru
        if ($request->tanggal_kadaluarsa !== $obat->tanggal_kadaluarsa) {
            Obat::create([
                'nama_obat' => $request->nama_obat,
                'stok' => $request->stok,
                'harga' => $request->harga,
                'tanggal_masuk' => $request->tanggal_masuk,
                'tanggal_keluar' => $request->tanggal_keluar ?? null,
                'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
            ]);

            return redirect()->route('obat.index')->with('success', 'Obat baru berhasil ditambahkan karena tanggal kadaluarsa berubah.');
        }

        $obat->update([
            'nama_obat' => $request->nama_obat,
            'stok' => $request->stok,
            'harga' => $request->harga,
            'tanggal_masuk' => $request->tanggal_masuk,
            'tanggal_keluar' => $request->tanggal_keluar ?? null,
            'tanggal_kadaluarsa' => $request->tanggal_kadaluarsa,
        ]);

        return redirect()->route('obat.index')->with('success', 'Obat berhasil diperbarui');
    }

    public function destroy($id_obat)
    {
        Obat::destroy($id_obat);
        return redirect()->route('obat.index')->with('success', 'Obat berhasil dihapus');
    }

    public function ajaxSearch(Request $request)
    {
        $statusStok = strtolower($request->status_stok);
        $statusKadaluarsa = strtolower($request->status_kadaluarsa);

        $query = Obat::query();

        if ($request->nama) {
            $query->where('nama_obat', 'like', '%' . $request->nama . '%');
        }

        if ($request->tanggal) {
            $query->whereDate('tanggal_kadaluarsa', '<=', $request->tanggal);
        }

        // Ambil data dulu
        $obat = $query->orderBy('tanggal_kadaluarsa')->get();

        // Proses filter manual berdasarkan status stok dan kadaluarsa
        $filtered = $obat->filter(function ($item) use ($statusStok, $statusKadaluarsa) {
            $stok = $item->stok;
            $stokStatus = $stok == 0 ? 'habis' : ($stok <= 10 ? 'menipis' : 'tersedia');

            $now = \Carbon\Carbon::now();
            $exp = \Carbon\Carbon::parse($item->tanggal_kadaluarsa);
            $selisih = $now->diffInDays($exp, false); // ✅ arah waktu benar

            $kadaluarsaStatus = $selisih < 0 ? 'kadaluarsa' : ($selisih <= 30 ? 'hampir' : 'belum');

            $stokCocok = empty($statusStok) || $stokStatus === $statusStok;
            $kadaluarsaCocok = empty($statusKadaluarsa) || $kadaluarsaStatus === $statusKadaluarsa;

            return $stokCocok && $kadaluarsaCocok;
        })->values();

        // Map data untuk response JSON
        $result = $filtered->map(function ($item) {
            $stok = $item->stok;
            $stok_label = $stok == 0 ? 'Habis' : ($stok <= 10 ? 'Stok Menipis' : 'Tersedia');
            $stok_class = $stok == 0 ? 'badge-merah' : ($stok <= 10 ? 'badge-kuning' : 'badge-biru');

            $now = \Carbon\Carbon::now();
            $exp = \Carbon\Carbon::parse($item->tanggal_kadaluarsa);
            $selisih = $now->diffInDays($exp, false);
            $kadaluarsa_label = $selisih < 0 ? 'Kadaluarsa' : ($selisih <= 30 ? 'Hampir Kadaluarsa' : 'Belum Kadaluarsa');
            $kadaluarsa_class = $kadaluarsa_label === 'Kadaluarsa' ? 'badge-merah' :
                                ($kadaluarsa_label === 'Hampir Kadaluarsa' ? 'badge-kuning' : 'badge-biru');

            return [
                'id_obat' => $item->id_obat,
                'nama_obat' => $item->nama_obat,
                'stok' => $item->stok,
                'harga' => $item->harga,
                'tanggal_masuk' => $item->tanggal_masuk,
                'tanggal_kadaluarsa' => $item->tanggal_kadaluarsa,
                'stok_label' => $stok_label,
                'stok_class' => $stok_class,
                'kadaluarsa_label' => $kadaluarsa_label,
                'kadaluarsa_class' => $kadaluarsa_class
            ];
        });

        return response()->json($result->values());
    }

}
