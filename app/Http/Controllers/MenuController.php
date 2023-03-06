<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Image;


class MenuController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $data = Menu::select('id', 'nama_menu', 'kategori', 'foto', 'harga', 'keterangan')
            ->when($search, function ($q, $search) {
                return $q->where('nama_menu', 'like', "%{$search}%");
            })
            ->orderBy('kategori')
            ->paginate(50);
        $data->map(function ($row) {
            $row->foto = asset("images/{$row->foto}");
            return $row;
        });
        return view('menu.index', [
            'data' => $data
        ]);
    }
    public function create()
    {
        return view('menu.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|min:4',
            'harga' => 'required|numeric',
            'file_foto' => 'required|image|max:2000',
            'kategori' => 'required|in:makanan,minuman'
        ]);
        $folder = 'images';
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        $file = $request->file('file_foto');
        $ext = $file->getClientOriginalExtension();
        $filename = date('Ymdhis') . '.' . $ext;
        $img = Image::make($file);
        $img->fit(300, 200);
        $img->save($folder . '/' . $filename);
        $request->merge([
            'foto' => $filename,
        ]);
        Menu::create($request->all());
        return to_route('menu.index')->with('status', 'save');
    }
    public function show(Menu $menu)
    {
        return abort(404);
    }
    public function edit(Menu $menu)
    {
        $menu->foto = asset("images/{$menu->foto}");
        return view('menu.edit', [
            'row' => $menu
        ]);
    }
    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'nama_menu' => 'required|min:4',
            'harga' => 'required|numeric',
            'file_foto' => 'nullable|image|max:2000',
            'kategori' => 'required|in:makanan,minuman'
        ]);
        if ($request->file_foto) {

            $folder = 'images';
            $foto_lama = "{$folder}/{$menu->foto}";
            if (file_exists($foto_lama)) {
                unlink($foto_lama);
            }
            $file = $request->file('file_foto');
            $ext = $file->getClientOriginalExtension();
            $filename = date('Ymdhis') . '.' . $ext;
            $img = Image::make($file);
            $img->fit(300, 200);
            $img->save($folder . '/' . $filename);
            $request->merge([
                'foto' => $filename,
            ]);
        }
        $menu->update($request->all());
        return to_route('menu.index')->with('status', 'edit');
    }
    public function destroy(Menu $menu)
    {
        $folder = 'images';
        $foto_lama = "{$folder}/{$menu->foto}";
        if (file_exists($foto_lama)) {
            unlink($foto_lama);
        }
        $menu->delete();
        return back()->with('status', 'delete');
    }
}
