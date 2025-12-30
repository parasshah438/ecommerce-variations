<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Display a listing of the addresses.
     */
    public function index()
    {
        return view('addresses.index');
    }

    /**
     * Get addresses data for DataTables.
     */
    public function data()
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        $data = [];
        foreach ($addresses as $address) {
            // Type badge
            $badgeClass = match($address->type) {
                'home' => 'bg-primary',
                'work' => 'bg-success', 
                'other' => 'bg-info',
                default => 'bg-secondary'
            };
            $typeBadge = '<span class="badge ' . $badgeClass . '">' . ucfirst($address->type) . '</span>';

            // Default badge/button
            if ($address->is_default) {
                $defaultBadge = '<span class="badge bg-warning"><i class="bi bi-star-fill me-1"></i>Default</span>';
            } else {
                $defaultBadge = '<button class="btn btn-outline-warning btn-sm set-default-btn" data-id="' . $address->id . '">
                                    <i class="bi bi-star me-1"></i>Set Default
                                </button>';
            }

            // Actions
            $actions = '
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm edit-btn" 
                            data-id="' . $address->id . '"
                            data-bs-toggle="tooltip" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm delete-btn" 
                            data-id="' . $address->id . '"
                            data-name="' . $address->full_name . '"
                            data-bs-toggle="tooltip" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>';

            $data[] = [
                'id' => $address->id,
                'full_name' => $address->full_name,
                'full_address' => $address->full_address,
                'type' => $address->type,
                'type_badge' => $typeBadge,
                'phone' => $address->phone ?? '-',
                'is_default' => $address->is_default,
                'default_badge' => $defaultBadge,
                'actions' => $actions,
                'created_at' => $address->created_at->format('Y-m-d H:i:s')
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:home,work,other',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        
        // Map form fields to database fields
        $addressData = [
            'user_id' => auth()->id(),
            'type' => $validatedData['type'],
            'name' => trim($validatedData['first_name'] . ' ' . $validatedData['last_name']),
            'phone' => $validatedData['phone'],
            'address_line' => $validatedData['address_line_1'] . ($validatedData['address_line_2'] ? ', ' . $validatedData['address_line_2'] : ''),
            'city' => $validatedData['city'],
            'state' => $validatedData['state'],
            'zip' => $validatedData['postal_code'],
            'country' => $validatedData['country'] ?? 'India',
            'landmark' => $validatedData['company'] ?? null, // Using company field as landmark for now
        ];
        
        // Handle checkbox - if not present or false, set to false, otherwise true
        $addressData['is_default'] = $request->has('is_default') && $request->input('is_default') ? true : false;

        // If this address is set as default, unset others
        if ($addressData['is_default']) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        $address = Address::create($addressData);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully!'
        ]);
    }

    /**
     * Show the form for editing the specified address.
     */
    public function edit(Address $address)
    {
        // Ensure user can only edit their own addresses
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Convert database fields back to form fields
        $addressLineParts = explode(', ', $address->address_line, 2);
        
        $formData = [
            'id' => $address->id,
            'type' => $address->type,
            'name' => $address->name,
            'company' => $address->landmark ?? '',
            'address_line_1' => $addressLineParts[0] ?? $address->address_line,
            'address_line_2' => $addressLineParts[1] ?? '',
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->zip,
            'country' => $address->country,
            'phone' => $address->phone,
            'is_default' => $address->is_default,
        ];

        return response()->json([
            'success' => true,
            'data' => $formData
        ]);
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, Address $address)
    {
        // Ensure user can only update their own addresses
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:home,work,other',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        
        // Map form fields to database fields
        $addressData = [
            'type' => $validatedData['type'],
            'name' => trim($validatedData['first_name'] . ' ' . $validatedData['last_name']),
            'phone' => $validatedData['phone'],
            'address_line' => $validatedData['address_line_1'] . ($validatedData['address_line_2'] ? ', ' . $validatedData['address_line_2'] : ''),
            'city' => $validatedData['city'],
            'state' => $validatedData['state'],
            'zip' => $validatedData['postal_code'],
            'country' => $validatedData['country'] ?? 'India',
            'landmark' => $validatedData['company'] ?? null, // Using company field as landmark for now
        ];
        
        // Handle checkbox - if not present or false, set to false, otherwise true
        $isDefault = $request->has('is_default') && $request->input('is_default') ? true : false;
        $addressData['is_default'] = $isDefault;

        // If this address is set as default, unset others
        if ($isDefault && !$address->is_default) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($addressData);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully!'
        ]);
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address)
    {
        // Ensure user can only delete their own addresses
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Check if this is the only address
        if (auth()->user()->addresses()->count() === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only address. Please add another address first.'
            ], 422);
        }

        $isDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set another as default
        if ($isDefault) {
            $newDefault = auth()->user()->addresses()->first();
            if ($newDefault) {
                $newDefault->setAsDefault();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully!'
        ]);
    }

    /**
     * Set address as default.
     */
    public function setDefault(Address $address)
    {
        // Ensure user can only modify their own addresses
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $address->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully!'
        ]);
    }
}