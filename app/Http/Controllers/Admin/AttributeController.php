<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes
     */
    public function index(Request $request)
    {
        $query = Attribute::with('attributeValues');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }
        
        $attributes = $query->paginate(10)->appends($request->all());
        
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute
     */
    public function create()
    {
        return view('admin.attributes.create');
    }

    /**
     * Store a newly created attribute
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
            'type' => 'required|in:text,color,size,number',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean'
        ]);

        $attribute = Attribute::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_required' => $request->boolean('is_required'),
            'is_filterable' => $request->boolean('is_filterable')
        ]);

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute created successfully!');
    }

    /**
     * Display the specified attribute
     */
    public function show(Attribute $attribute)
    {
        $attribute->load('attributeValues');
        return view('admin.attributes.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified attribute
     */
    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute
     */
    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attributes', 'name')->ignore($attribute->id)
            ],
            'type' => 'required|in:text,color,size,number',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean'
        ]);

        $attribute->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_required' => $request->boolean('is_required'),
            'is_filterable' => $request->boolean('is_filterable')
        ]);

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute updated successfully!');
    }

    /**
     * Remove the specified attribute
     */
    public function destroy(Attribute $attribute)
    {
        // Check if attribute has values
        if ($attribute->attributeValues()->count() > 0) {
            return redirect()->route('admin.attributes.index')
                ->with('error', 'Cannot delete attribute with existing values. Delete values first.');
        }

        $attribute->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully!');
    }
}
