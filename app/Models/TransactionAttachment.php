<?php
// app/Models/TransactionAttachment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TransactionAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'file_name',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // ================================
    // RELATIONSHIPS
    // ================================

    /**
     * Attachment belongs to transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // ================================
    // ACCESSORS & MUTATORS
    // ================================

    /**
     * Get file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get file extension
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is image
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is PDF
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get file type icon
     */
    public function getFileIconAttribute(): string
    {
        if ($this->is_image) {
            return 'fas fa-image';
        } elseif ($this->is_pdf) {
            return 'fas fa-file-pdf';
        } elseif (str_contains($this->mime_type, 'text')) {
            return 'fas fa-file-alt';
        } elseif (str_contains($this->mime_type, 'spreadsheet') || str_contains($this->mime_type, 'excel')) {
            return 'fas fa-file-excel';
        } elseif (str_contains($this->mime_type, 'word')) {
            return 'fas fa-file-word';
        } else {
            return 'fas fa-file';
        }
    }

    // ================================
    // BUSINESS LOGIC METHODS
    // ================================

    /**
     * Check if file exists in storage
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::delete($this->file_path);
        }
        return true;
    }

    /**
     * Get download response
     */
    public function download()
    {
        if (!$this->fileExists()) {
            abort(404, 'File not found');
        }

        return Storage::download($this->file_path, $this->original_name);
    }

    // ================================
    // MODEL EVENTS
    // ================================

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Delete file when attachment is deleted
        static::deleting(function ($attachment) {
            $attachment->deleteFile();
        });
    }

    // ================================
    // SCOPES
    // ================================

    /**
     * Scope for images only
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope for PDFs only
     */
    public function scopePdfs($query)
    {
        return $query->where('mime_type', 'application/pdf');
    }

    /**
     * Scope by file size range
     */
    public function scopeBySizeRange($query, int $minSize, int $maxSize)
    {
        return $query->whereBetween('file_size', [$minSize, $maxSize]);
    }
}