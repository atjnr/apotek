@extends('layouts.app')

@section('title', 'Tambah Resep')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
@endsection

@section('content')
<div class="form-card">
    <form action="{{ route('resep.store') }}" method="POST">
        @csrf

        <h2>Tambah Resep</h2>

        <div class="form-row">
            <div class="form-group">
                <label for="nama_pasien">Nama Pasien</label>
                <input type="text" name="nama_pasien" id="nama_pasien" required>
            </div>

            <div class="form-group">
                <label for="tanggal_resep">Tanggal Resep</label>
                <input type="date" name="tanggal_resep" id="tanggal_resep" required>
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <input type="text" name="keterangan" id="keterangan" placeholder="Opsional">
            </div>
        </div>

        <h4>Detail Obat</h4>

        <div id="obat-wrapper">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Obat</label>
                    <select name="obat_id[]" required>
                        <option value="">-- Pilih Obat --</option>
                        @foreach ($obat as $item)
                            <option value="{{ $item->id_obat }}">{{ $item->nama_obat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah[]" min="1" required>
                </div>

                <div class="form-group">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan_obat[]" placeholder="Opsional">
                </div>
            </div>
        </div>

        <button type="button" class="btn-submit" onclick="tambahObat()">+ Tambah Obat</button>

        <div class="form-actions">
            <a href="{{ route('resep.index') }}" class="btn-back">Kembali</a>
            <button type="submit" class="btn-submit">Simpan Resep</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function tambahObat() {
        const wrapper = document.getElementById('obat-wrapper');
        const html = `
        <div class="form-row">
            <div class="form-group">
                <label>Nama Obat</label>
                <select name="obat_id[]" required>
                    <option value="">-- Pilih Obat --</option>
                    @foreach ($obat as $item)
                        <option value="{{ $item->id_obat }}">{{ $item->nama_obat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Jumlah</label>
                <input type="number" name="jumlah[]" min="1" required>
            </div>

            <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="keterangan_obat[]" placeholder="Opsional">
            </div>
        </div>`;
        wrapper.insertAdjacentHTML('beforeend', html);
    }
</script>
@endsection
