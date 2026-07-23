<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IntegrationResource\Pages;
use App\Models\Integration;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return Heroicon::OutlinedCpuChip;
    }

    protected static ?string $navigationLabel = 'Integrations';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->required()
                    ->default(auth()->user()->outlet_id),

                Select::make('name')
                    ->label('Nama Integrasi')
                    ->options([
                        'midtrans' => 'Midtrans',
                        'xendit' => 'Xendit',
                        'fonnte' => 'Fonnte (WhatsApp)',
                        'wablas' => 'Wablas (WhatsApp)',
                        'whatsapp_business' => 'WhatsApp Business API',
                    ])
                    ->required()
                    ->live(),

                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'payment_gateway' => 'Payment Gateway',
                        'whatsapp_gateway' => 'WhatsApp Gateway',
                        'shipping' => 'Shipping',
                        'accounting' => 'Accounting',
                        'pos_sync' => 'POS Sync',
                    ])
                    ->required(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

                Toggle::make('is_primary')
                    ->label('Primary')
                    ->default(false)
                    ->helperText('Jadikan sebagai primary untuk tipe ini'),

                Toggle::make('test_mode')
                    ->label('Test Mode')
                    ->default(true),

                KeyValue::make('config')
                    ->label('Konfigurasi')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->addActionLabel('Tambah Konfigurasi')
                    ->reorderable(false)
                    ->default([
                        'client_key' => '',
                        'server_key' => '',
                        'merchant_id' => '',
                        'api_key' => '',
                        'is_production' => false,
                        'callback_url' => '',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->badge()
                    ->colors([
                        'primary' => 'midtrans',
                        'success' => 'xendit',
                        'info' => 'fonnte',
                        'warning' => 'wablas',
                        'gray' => 'whatsapp_business',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'success' => 'payment_gateway',
                        'info' => 'whatsapp_gateway',
                        'warning' => 'shipping',
                        'gray' => 'accounting',
                        'primary' => 'pos_sync',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'payment_gateway' => 'Payment Gateway',
                        'whatsapp_gateway' => 'WhatsApp Gateway',
                        'shipping' => 'Shipping',
                        'accounting' => 'Accounting',
                        'pos_sync' => 'POS Sync',
                        default => $state,
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),

                IconColumn::make('test_mode')
                    ->label('Test Mode')
                    ->boolean(),

                TextColumn::make('last_sync_at')
                    ->label('Last Sync')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('last_error')
                    ->label('Last Error')
                    ->limit(30)
                    ->color('danger'),
            ])
            ->filters([
                SelectFilter::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name'),

                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'payment_gateway' => 'Payment Gateway',
                        'whatsapp_gateway' => 'WhatsApp Gateway',
                        'shipping' => 'Shipping',
                        'accounting' => 'Accounting',
                        'pos_sync' => 'POS Sync',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIntegrations::route('/'),
            'create' => Pages\CreateIntegration::route('/create'),
            'edit' => Pages\EditIntegration::route('/{record}/edit'),
        ];
    }
}