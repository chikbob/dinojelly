<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->through(fn (Collection $collection) => [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'is_active' => $collection->is_active,
                'sort_order' => $collection->sort_order,
                'products_count' => $collection->products_count,
            ]);

        return Inertia::render('admin/Collections/Index', [
            'collections' => $collections,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/Collections/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:collections,slug'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'image_url' => ['nullable', 'url'],
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('collections', 'public');
        } elseif (!empty($validated['image_url'])) {
            $data['image'] = $validated['image_url'];
        }

        Collection::query()->create($data);

        return redirect()->route('admin.collections.index')->with('success', 'Collection created');
    }

    public function edit(Collection $collection)
    {
        return Inertia::render('admin/Collections/Edit', [
            'collection' => $collection,
        ]);
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:collections,slug,' . $collection->id],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'image_url' => ['nullable', 'url'],
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? false,
        ];

        if ($request->hasFile('image')) {
            if ($collection->image && !filter_var($collection->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($collection->image);
            }

            $data['image'] = $request->file('image')->store('collections', 'public');
        } elseif (!empty($validated['image_url'])) {
            $data['image'] = $validated['image_url'];
        }

        $collection->update($data);

        return redirect()->route('admin.collections.index')->with('success', 'Collection updated');
    }

    public function destroy(Collection $collection)
    {
        if ($collection->image && !filter_var($collection->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($collection->image);
        }

        $collection->delete();

        return redirect()->route('admin.collections.index')->with('success', 'Collection deleted');
    }
}
