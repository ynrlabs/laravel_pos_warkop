<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    use HasFactory;

    protected $table = 'integrations';

    protected $fillable = [
        'outlet_id',
        'name',
        'type',
        'config',
        'is_active',
        'is_primary',
        'test_mode',
        'last_sync_at',
        'last_error',
    ];

    protected $casts = [
        'config' => 'encrypted:array',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'test_mode' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function scopePaymentGateway($query)
    {
        return $query->where('type', 'payment_gateway');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function getDecryptedConfig(): array
    {
        return $this->config ?? [];
    }

    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        $config = $this->getDecryptedConfig();
        return $config[$key] ?? $default;
    }

    public function isMidtrans(): bool
    {
        return $this->name === 'midtrans';
    }

    public function isXendit(): bool
    {
        return $this->name === 'xendit';
    }

    public function getClientKey(): ?string
    {
        return $this->getConfigValue('client_key');
    }

    public function getServerKey(): ?string
    {
        return $this->getConfigValue('server_key');
    }

    public function getMerchantId(): ?string
    {
        return $this->getConfigValue('merchant_id');
    }

    public function getApiKey(): ?string
    {
        return $this->getConfigValue('api_key');
    }

    public function getCallbackUrl(): ?string
    {
        return $this->getConfigValue('callback_url');
    }

    public function isProduction(): bool
    {
        return !$this->test_mode && ($this->getConfigValue('is_production') ?? false);
    }
}