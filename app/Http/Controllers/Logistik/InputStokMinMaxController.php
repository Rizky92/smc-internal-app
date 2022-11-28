<?php

namespace App\Http\Controllers\Logistik;

use App\Http\Controllers\Controller;
use App\MinmaxBarangNonMedis;
use Illuminate\Http\Request;

class InputStokMinMaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return view('admin.logistik.minmax.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MinmaxBarangNonMedis $barang
     * @return \Illuminate\Http\Response
     */
    public function show(MinmaxBarangNonMedis $barang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MinmaxBarangNonMedis $barang
     * @return \Illuminate\Http\Response
     */
    public function edit(MinmaxBarangNonMedis $barang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MinmaxBarangNonMedis $barang
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MinmaxBarangNonMedis $barang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MinmaxBarangNonMedis $barang
     * @return \Illuminate\Http\Response
     */
    public function destroy(MinmaxBarangNonMedis $barang)
    {
        //
    }
}
