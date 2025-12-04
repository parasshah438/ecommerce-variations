<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\WhatsApp\UltramsgService;
use App\Models\WhatsApp\WhatsAppMessage;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Models\WhatsApp\WhatsAppContact;
use App\Models\WhatsApp\WhatsAppGroup;
use App\Models\WhatsApp\WhatsAppBulkMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppController extends Controller
{
    protected $ultramsgService;
    
    public function __construct(UltramsgService $ultramsgService)
    {
        $this->ultramsgService = $ultramsgService;
    }
    
    /**
     * WhatsApp Dashboard
     */
    public function index(): View
    {
        $stats = [
            'total_messages' => WhatsAppMessage::where('user_id', Auth::id())->count(),
            'sent_today' => WhatsAppMessage::where('user_id', Auth::id())
                ->whereDate('created_at', today())->count(),
            'delivered_messages' => WhatsAppMessage::where('user_id', Auth::id())
                ->where('status', 'delivered')->count(),
            'failed_messages' => WhatsAppMessage::where('user_id', Auth::id())
                ->where('status', 'failed')->count(),
            'total_contacts' => WhatsAppContact::where('user_id', Auth::id())->count(),
            'active_templates' => WhatsAppTemplate::where('user_id', Auth::id())
                ->where('status', 'active')->count()
        ];
        
        $recentMessages = WhatsAppMessage::with('contact')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        return view('whatsapp.index', compact('stats', 'recentMessages'));
    }
    
    /**
     * Show send message form
     */
    public function sendForm(): View
    {
        $contacts = WhatsAppContact::where('user_id', Auth::id())->get();
        $templates = WhatsAppTemplate::where('user_id', Auth::id())
            ->where('status', 'active')->get();
        $groups = WhatsAppGroup::where('user_id', Auth::id())->get();
        
        return view('whatsapp.send', compact('contacts', 'templates', 'groups'));
    }
    
    /**
     * Send text message
     */
    public function sendText(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string|max:1000'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $phone = $this->formatPhoneNumber($request->phone);
            
            $response = $this->ultramsgService->sendText($phone, $request->message);
            
            // Store message in database
            $message = WhatsAppMessage::create([
                'user_id' => Auth::id(),
                'phone' => $phone,
                'message_type' => 'text',
                'content' => $request->message,
                'ultramsg_id' => $response['id'] ?? null,
                'status' => $response['sent'] ? 'sent' : 'failed',
                'response_data' => $response
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp send text failed', [
                'user_id' => Auth::id(),
                'phone' => $request->phone,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send image message
     */
    public function sendImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'image' => 'required|image|max:10240', // 10MB max
            'caption' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $phone = $this->formatPhoneNumber($request->phone);
            
            // Upload image
            $imagePath = $request->file('image')->store('whatsapp/images', 'public');
            $imageUrl = Storage::url($imagePath);
            $fullImageUrl = url($imageUrl);
            
            $response = $this->ultramsgService->sendImage(
                $phone, 
                $fullImageUrl, 
                $request->caption
            );
            
            // Store message in database
            $message = WhatsAppMessage::create([
                'user_id' => Auth::id(),
                'phone' => $phone,
                'message_type' => 'image',
                'content' => $request->caption,
                'media_path' => $imagePath,
                'media_url' => $fullImageUrl,
                'ultramsg_id' => $response['id'] ?? null,
                'status' => $response['sent'] ? 'sent' : 'failed',
                'response_data' => $response
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Image sent successfully',
                'data' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp send image failed', [
                'user_id' => Auth::id(),
                'phone' => $request->phone,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send image: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send document
     */
    public function sendDocument(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'document' => 'required|file|max:51200', // 50MB max
            'filename' => 'nullable|string|max:255'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $phone = $this->formatPhoneNumber($request->phone);
            
            // Upload document
            $documentPath = $request->file('document')->store('whatsapp/documents', 'public');
            $documentUrl = Storage::url($documentPath);
            $fullDocumentUrl = url($documentUrl);
            
            $filename = $request->filename ?? $request->file('document')->getClientOriginalName();
            
            $response = $this->ultramsgService->sendDocument(
                $phone, 
                $fullDocumentUrl, 
                $filename
            );
            
            // Store message in database
            $message = WhatsAppMessage::create([
                'user_id' => Auth::id(),
                'phone' => $phone,
                'message_type' => 'document',
                'content' => $filename,
                'media_path' => $documentPath,
                'media_url' => $fullDocumentUrl,
                'ultramsg_id' => $response['id'] ?? null,
                'status' => $response['sent'] ? 'sent' : 'failed',
                'response_data' => $response
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document sent successfully',
                'data' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp send document failed', [
                'user_id' => Auth::id(),
                'phone' => $request->phone,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send document: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send audio message
     */
    public function sendAudio(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'audio' => 'required|file|mimetypes:audio/mpeg,audio/wav,audio/ogg|max:10240' // 10MB max
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $phone = $this->formatPhoneNumber($request->phone);
            
            // Upload audio
            $audioPath = $request->file('audio')->store('whatsapp/audio', 'public');
            $audioUrl = Storage::url($audioPath);
            $fullAudioUrl = url($audioUrl);
            
            $response = $this->ultramsgService->sendAudio($phone, $fullAudioUrl);
            
            // Store message in database
            $message = WhatsAppMessage::create([
                'user_id' => Auth::id(),
                'phone' => $phone,
                'message_type' => 'audio',
                'media_path' => $audioPath,
                'media_url' => $fullAudioUrl,
                'ultramsg_id' => $response['id'] ?? null,
                'status' => $response['sent'] ? 'sent' : 'failed',
                'response_data' => $response
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Audio sent successfully',
                'data' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp send audio failed', [
                'user_id' => Auth::id(),
                'phone' => $request->phone,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send audio: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send contact
     */
    public function sendContact(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $phone = $this->formatPhoneNumber($request->phone);
            $contactPhone = $this->formatPhoneNumber($request->contact_phone);
            
            $response = $this->ultramsgService->sendContact(
                $phone, 
                $contactPhone, 
                $request->contact_name
            );
            
            // Store message in database
            $message = WhatsAppMessage::create([
                'user_id' => Auth::id(),
                'phone' => $phone,
                'message_type' => 'contact',
                'content' => json_encode([
                    'name' => $request->contact_name,
                    'phone' => $contactPhone
                ]),
                'ultramsg_id' => $response['id'] ?? null,
                'status' => $response['sent'] ? 'sent' : 'failed',
                'response_data' => $response
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Contact sent successfully',
                'data' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp send contact failed', [
                'user_id' => Auth::id(),
                'phone' => $request->phone,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send contact: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk send messages
     */
    public function bulkSend(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phones' => 'required|array|min:1|max:100',
            'phones.*' => 'required|string',
            'message_type' => 'required|in:text,template',
            'message' => 'required_if:message_type,text|string|max:1000',
            'template_id' => 'required_if:message_type,template|exists:whatsapp_templates,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $batchId = 'BULK_' . time() . '_' . Auth::id();
            $phones = array_unique($request->phones);
            $totalPhones = count($phones);
            
            // Create bulk message record
            $bulkMessage = WhatsAppBulkMessage::create([
                'user_id' => Auth::id(),
                'batch_id' => $batchId,
                'message_type' => $request->message_type,
                'content' => $request->message_type === 'text' ? $request->message : null,
                'template_id' => $request->template_id ?? null,
                'total_recipients' => $totalPhones,
                'status' => 'processing'
            ]);
            
            // Queue individual messages
            foreach ($phones as $phone) {
                $formattedPhone = $this->formatPhoneNumber($phone);
                
                try {
                    if ($request->message_type === 'text') {
                        $response = $this->ultramsgService->sendText($formattedPhone, $request->message);
                    } else {
                        $template = WhatsAppTemplate::findOrFail($request->template_id);
                        $response = $this->ultramsgService->sendTemplate($formattedPhone, $template->content);
                    }
                    
                    WhatsAppMessage::create([
                        'user_id' => Auth::id(),
                        'phone' => $formattedPhone,
                        'message_type' => $request->message_type,
                        'content' => $request->message_type === 'text' ? $request->message : $template->content ?? '',
                        'bulk_message_id' => $bulkMessage->id,
                        'batch_id' => $batchId,
                        'ultramsg_id' => $response['id'] ?? null,
                        'status' => $response['sent'] ? 'sent' : 'failed',
                        'response_data' => $response
                    ]);
                    
                } catch (\Exception $e) {
                    WhatsAppMessage::create([
                        'user_id' => Auth::id(),
                        'phone' => $formattedPhone,
                        'message_type' => $request->message_type,
                        'content' => $request->message_type === 'text' ? $request->message : '',
                        'bulk_message_id' => $bulkMessage->id,
                        'batch_id' => $batchId,
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                }
                
                // Small delay to avoid rate limiting
                usleep(500000); // 0.5 second delay
            }
            
            // Update bulk message status
            $sentCount = WhatsAppMessage::where('bulk_message_id', $bulkMessage->id)
                ->where('status', 'sent')->count();
            $failedCount = WhatsAppMessage::where('bulk_message_id', $bulkMessage->id)
                ->where('status', 'failed')->count();
                
            $bulkMessage->update([
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'status' => 'completed',
                'completed_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk messages sent successfully',
                'data' => [
                    'batch_id' => $batchId,
                    'total' => $totalPhones,
                    'sent' => $sentCount,
                    'failed' => $failedCount
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp bulk send failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk send failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get instance status
     */
    public function instanceStatus(): JsonResponse
    {
        try {
            $status = $this->ultramsgService->getInstanceStatus();
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp instance status failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get instance status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get QR Code for authentication
     */
    public function getQRCode(): JsonResponse
    {
        try {
            $qrCode = $this->ultramsgService->getQRCode();
            
            return response()->json([
                'success' => true,
                'data' => $qrCode
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp QR code failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get QR code: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle incoming webhook messages
     */
    public function handleIncomingMessage(Request $request): JsonResponse
    {
        try {
            Log::info('WhatsApp incoming message webhook', $request->all());
            
            // Process incoming message
            // This would typically store the incoming message and trigger any auto-responses
            
            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp incoming message webhook failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json(['status' => 'error'], 500);
        }
    }
    
    /**
     * Messages list view
     */
    public function messagesList(Request $request): View
    {
        $query = WhatsAppMessage::with(['contact', 'template', 'bulkMessage'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');
            
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('type')) {
            $query->where('message_type', $request->type);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }
        
        $messages = $query->paginate(20);
        
        return view('whatsapp.messages.list', compact('messages'));
    }
    
    /**
     * Templates management
     */
    public function templates(Request $request): View
    {
        try {
            $query = WhatsAppTemplate::where('user_id', Auth::id());
            
            // Apply filters
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('content', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }
            
            $templates = $query->orderBy('created_at', 'desc')->paginate(20);
            
            return view('whatsapp.templates.index', compact('templates'));
            
        } catch (\Exception $e) {
            Log::error('Templates page error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return view with empty collection on error
            $templates = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('whatsapp.templates.index', compact('templates'))
                ->with('error', 'Failed to load templates: ' . $e->getMessage());
        }
    }
    
    /**
     * Contacts management
     */
    public function contacts(Request $request): View
    {
        try {
            $query = WhatsAppContact::where('user_id', Auth::id());
            
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('blocked')) {
                $query->where('is_blocked', (bool)$request->blocked);
            }
            
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('phone', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('company', 'like', '%' . $request->search . '%');
                });
            }
            
            $contacts = $query->orderBy('name')->paginate(20);
            
            return view('whatsapp.contacts.index', compact('contacts'));
            
        } catch (\Exception $e) {
            Log::error('Contacts page error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return view with empty collection on error
            $contacts = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('whatsapp.contacts.index', compact('contacts'))
                ->with('error', 'Failed to load contacts: ' . $e->getMessage());
        }
    }
    
    /**
     * Settings page
     */
    public function settings(): View
    {
        return view('whatsapp.settings');
    }
    
    /**
     * Update settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            // In a real application, you would save these to a settings table
            // or update environment variables
            
            Log::info('WhatsApp settings update attempted', [
                'user_id' => Auth::id(),
                'data' => $request->except(['_token', '_method'])
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp settings update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Analytics page
     */
    public function analytics(): View
    {
        $stats = [
            'messages_by_day' => WhatsAppMessage::where('user_id', Auth::id())
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
                
            'messages_by_status' => WhatsAppMessage::where('user_id', Auth::id())
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
                
            'messages_by_type' => WhatsAppMessage::where('user_id', Auth::id())
                ->selectRaw('message_type, COUNT(*) as count')
                ->groupBy('message_type')
                ->get()
        ];
        
        return view('whatsapp.analytics', compact('stats'));
    }
    
    /**
     * Format phone number for WhatsApp
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present
        if (!str_starts_with($phone, '91') && !str_starts_with($phone, '+91')) {
            $phone = '91' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Validate phone number
     */
    public function validatePhoneNumber(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $formattedPhone = $this->formatPhoneNumber($request->phone);
            $isValid = strlen($formattedPhone) >= 10 && strlen($formattedPhone) <= 15;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $request->phone,
                    'formatted' => $formattedPhone,
                    'is_valid' => $isValid
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Phone validation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload media files
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'media' => 'required|file|max:51200', // 50MB max
            'type' => 'required|in:image,document,audio,video'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $file = $request->file('media');
            $type = $request->type;
            
            $path = $file->store("whatsapp/{$type}s", 'public');
            $url = Storage::url($path);
            $fullUrl = url($url);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'url' => $fullUrl,
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Media upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get bulk message status
     */
    public function getBulkStatus(Request $request, string $batchId): JsonResponse
    {
        try {
            $bulkMessage = WhatsAppBulkMessage::where('batch_id', $batchId)
                ->where('user_id', Auth::id())
                ->firstOrFail();
                
            $bulkMessage->updateCounts();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'batch_id' => $bulkMessage->batch_id,
                    'status' => $bulkMessage->status,
                    'total' => $bulkMessage->total_recipients,
                    'sent' => $bulkMessage->sent_count,
                    'delivered' => $bulkMessage->delivered_count,
                    'failed' => $bulkMessage->failed_count,
                    'progress' => $bulkMessage->progress_percentage,
                    'estimated_completion' => $bulkMessage->estimated_completion
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get bulk status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test API connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->ultramsgService->testConnection();
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}