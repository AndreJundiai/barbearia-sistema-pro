<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        $images = GalleryImage::latest()->get();
        return view('gallery.index', compact('images'));
    }

    public function create()
    {
        return view('gallery.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('gallery', 'public');
        
        GalleryImage::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'path' => $path,
        ]);

        return redirect()->route('gallery.index')->with('status', 'Foto adicionada com sucesso!');
    }
}
