<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImportBatch extends Model
{
    use HasFactory;

    protected $table = 'product_import_batches';

    protected $fillable = [
        'batch_uuid',
        'original_filename',
        'file_path',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'processed_rows',
        'failed_rows',
        'validation_errors',
        'import_errors',
        'created_by',
    ];

    protected $casts = [
        'validation_errors' => 'array',
        'import_errors' => 'array',
        'total_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
        'processed_rows' => 'integer',
        'failed_rows' => 'integer',
        'created_by' => 'integer',
    ];

    const STATUS_UPLOADED = 'uploaded';
    const STATUS_VALIDATING = 'validating';
    const STATUS_VALIDATED = 'validated';
    const STATUS_IMPORTING = 'importing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_rows <= 0) {
            return 0;
        }
        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }

    public function addValidationError(int $rowNumber, string $message): void
    {
        $errors = $this->validation_errors ?? [];
        $errors[] = [
            'row' => $rowNumber,
            'message' => $message,
        ];
        $this->validation_errors = $errors;
        $this->save();
    }

    public function addImportError(int $rowNumber, string $message): void
    {
        $errors = $this->import_errors ?? [];
        $errors[] = [
            'row' => $rowNumber,
            'message' => $message,
        ];
        $this->import_errors = $errors;
        $this->save();
    }
}
