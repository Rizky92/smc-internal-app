<?php

namespace App\Http\Controllers\Logistik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InputStokMinMaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
     * @param  mixed $barang
     * @return \Illuminate\Http\Response
     */
    public function show($barang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  mixed $barang
     * @return \Illuminate\Http\Response
     */
    public function edit($barang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed $barang
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $barang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  mixed $barang
     * @return \Illuminate\Http\Response
     */
    public function destroy($barang)
    {
        //
    }
}
