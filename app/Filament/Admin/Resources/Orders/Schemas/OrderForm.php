<?php

namespace App\Filament\Admin\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('outlet_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('table_id')
                    ->numeric(),
                TextInput::make('tab_id')
                    ->numeric(),
                TextInput::make('order_type')
                    ->required()
                    ->default('dine_in'),
                TextInput::make('customer_name'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('service_charge')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('rounding_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('payment_method'),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
