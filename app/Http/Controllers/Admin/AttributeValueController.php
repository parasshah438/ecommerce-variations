<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeValueController extends Controller
{
    /**
     * Display a listing of attribute values
     */
    public function index(Request $request)
    {
        $query = AttributeValue::with('attribute');
        
        // Filter by attribute
        if ($request->filled('attribute_id')) {
            $query->where('attribute_id', $request->attribute_id);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('value', 'LIKE', "%{$search}%")
                  ->orWhere('hex_color', 'LIKE', "%{$search}%")
                  ->orWhereHas('attribute', function($attr) use ($search) {
                      $attr->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        $attributeValues = $query->paginate(10)->appends($request->all());
        $attributeValues->withPath($request->url());
        
        $attributes = Attribute::orderBy('name')->get();
        
        return view('admin.attribute-values.index', compact('attributeValues', 'attributes'));
    }

    /**
     * Show the form for creating a new attribute value
     */
    public function create()
    {
        $attributes = Attribute::orderBy('name')->get();
        return view('admin.attribute-values.create', compact('attributes'));
    }

    /**
     * Store a newly created attribute value
     */
    public function store(Request $request)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_values')->where(function ($query) use ($request) {
                    return $query->where('attribute_id', $request->attribute_id);
                })
            ],
            'hex_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_default' => 'boolean'
        ]);

        // If this is set as default, remove default from other values of same attribute
        if ($request->boolean('is_default')) {
            AttributeValue::where('attribute_id', $request->attribute_id)
                ->update(['is_default' => false]);
        }

        AttributeValue::create([
            'attribute_id' => $request->attribute_id,
            'value' => $request->value,
            'hex_color' => $request->hex_color,
            'is_default' => $request->boolean('is_default')
        ]);

        return redirect()->route('admin.attribute-values.index')
            ->with('success', 'Attribute value created successfully!');
    }

    /**
     * Display the specified attribute value
     */
    public function show(AttributeValue $attributeValue)
    {
        $attributeValue->load('attribute');
        return view('admin.attribute-values.show', compact('attributeValue'));
    }

    /**
     * Show the form for editing the specified attribute value
     */
    public function edit(AttributeValue $attributeValue)
    {
        $attributes = Attribute::orderBy('name')->get();
        return view('admin.attribute-values.edit', compact('attributeValue', 'attributes'));
    }

    /**
     * Update the specified attribute value
     */
    public function update(Request $request, AttributeValue $attributeValue)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_values')->where(function ($query) use ($request) {
                    return $query->where('attribute_id', $request->attribute_id);
                })->ignore($attributeValue->id)
            ],
            'hex_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_default' => 'boolean'
        ]);

        // If this is set as default, remove default from other values of same attribute
        if ($request->boolean('is_default')) {
            AttributeValue::where('attribute_id', $request->attribute_id)
                ->where('id', '!=', $attributeValue->id)
                ->update(['is_default' => false]);
        }

        $attributeValue->update([
            'attribute_id' => $request->attribute_id,
            'value' => $request->value,
            'hex_color' => $request->hex_color,
            'is_default' => $request->boolean('is_default')
        ]);

        return redirect()->route('admin.attribute-values.index')
            ->with('success', 'Attribute value updated successfully!');
    }

    /**
     * Remove the specified attribute value
     */
    public function destroy(AttributeValue $attributeValue)
    {
        // Check if this attribute value is used by any product variations
        if ($attributeValue->productVariations()->count() > 0) {
            return redirect()->route('admin.attribute-values.index')
                ->with('error', 'Cannot delete attribute value that is used by product variations.');
        }

        $attributeValue->delete();

        return redirect()->route('admin.attribute-values.index')
            ->with('success', 'Attribute value deleted successfully!');
    }
}
