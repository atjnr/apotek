@extends('layouts.app')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection

@section('content')

<div class="form-container">
    <h2 class="form-title">Tambah Transaksi</h2>

    @if ($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('transaksi.store') }}" method="POST" id="formTransaksi">
        @csrf

        <div class="form-group">
            <label for="id_obat">Nama Obat</label>
            <select name="id_obat" id="id_obat" required>
                <option value="">-- Pilih --</option>
                @foreach ($obat as $o)
                    <option value="{{ $o->id_obat }}" data-harga="{{ $o->harga }}">{{ $o->nama_obat }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="jenis">Jenis Transaksi</label>
            <select name="jenis" id="jenis" required>
                <option value="">-- Pilih --</option>
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
            </select>
        </div>

        <div class="form-group">
            <label for="jumlah">Jumlah</label>
            <input type="number" name="jumlah" id="jumlah" min="1" required>
        </div>

        <div class="form-group" id="tanggalKadaluarsaContainer" style="display: none;">
            <label for="tanggal_kadaluarsa">Tanggal Kadaluarsa</label>
            <input type="date" name="tanggal_kadaluarsa" id="tanggal_kadaluarsa">
        </div>

        <div class="form-group">
            <label for="totalHargaDisplay">Total Harga</label>
            <input type="text" id="totalHargaDisplay" readonly class="readonly">
            <input type="hidden" name="total" id="totalHarga">
        </div>

        <div class="form-group">
            <label for="tanggal_transaksi">Tanggal Transaksi</label>
            <input type="date" name="tanggal_transaksi" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="button-submit">Simpan</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function () {
    function updateTotal() {
        const harga = parseInt($('#id_obat option:selected').data('harga')) || 0;
        const jumlah = parseInt($('#jumlah').val()) || 0;
        const total = harga * jumlah;

        $('#totalHarga').val(total);
        $('#totalHargaDisplay').val('Rp' + total.toLocaleString('id-ID'));
    }

    $('#id_obat, #jumlah').on('change keyup', updateTotal);

    $('#jenis').on('change', function () {
        const jenis = $(this).val();
        if (jenis === 'masuk') {
            $('#tanggalKadaluarsaContainer').slideDown();
            $('#tanggal_kadaluarsa').attr('required', true);
        } else {
            $('#tanggalKadaluarsaContainer').slideUp();
            $('#tanggal_kadaluarsa').removeAttr('required').val('');
        }
    });

    $('#jenis').trigger('change'); // Trigger saat pertama kali load
});
</script>

@endsection
