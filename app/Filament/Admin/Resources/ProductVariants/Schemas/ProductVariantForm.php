<?php

namespace App\Filament\Admin\Resources\ProductVariants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('price_adjustment')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('stock_qty')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('track_stock')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
